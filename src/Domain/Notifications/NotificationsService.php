<?php

namespace App\Domain\Notifications;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Application\Payload\Payload;

class NotificationsService extends Service {

    private $translation;
    private $settings;
    private $user_notifications_service;
    private $cat_service;
    private $client_service;
    private $helper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            Translator $translation,
            Settings $settings,
            NotificationsMapper $mapper,
            Users\NotificationUsersService $user_notifications_service,
            Categories\NotificationCategoryService $cat_service,
            Clients\NotificationClientsService $client_service,
            Helper $helper) {
        parent::__construct($logger, $user);
        $this->translation = $translation;
        $this->settings = $settings;
        $this->mapper = $mapper;
        $this->user_notifications_service = $user_notifications_service;
        $this->cat_service = $cat_service;
        $this->client_service = $client_service;
        $this->helper = $helper;
    }

    public function getCategoriesOfCurrentUser() {
        $user = $this->current_user->getUser();
        return $this->user_notifications_service->getCategoriesByUser($user->id);
    }

    public function getUnreadNotificationsCountByUser() {
        $user = $this->current_user->getUser();
        if (!is_null($user)) {
            return $this->mapper->getUnreadNotificationsCountByUser($user->id);
        }
        return 0;
    }

    public function getNotifications($data) {

        //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $limit = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 5;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $user = $this->current_user->getUser();
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);

        $categories = $this->cat_service->getAllCategories();
        array_map(function($cat) {
            if ($cat->isInternal()) {
                $cat->name = $this->translation->getTranslatedString($cat->name);
            }
            return $cat;
        }, $categories);

        $result = [];
        $result["status"] = "success";
        $result["data"] = $notifications;
        $result["count"] = $this->mapper->getNotificationsCountByUser($user->id);
        $result["categories"] = $categories;

        // mark as seen
        if (is_array($notifications) && !empty($notifications)) {
            $notification_ids = array_map(function($el) {
                return $el->id;
            }, $notifications);
            $this->mapper->markAsSeen($notification_ids);
        }

        $result["unseen"] = $this->mapper->getUnreadNotificationsCountByUser($user->id);

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    private function sendNotificationsToUsersWithCategory($identifier, $title, $message) {
        try {
            $category_id = $this->cat_service->getCategoryByIdentifier($identifier);

            // Push Notifications
            $clients = $this->client_service->getClientsByCategory($category_id->id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message);
            }

            // Save notification for the users (frontend)
            $users = $this->user_notifications_service->getUsersByCategory($category_id->id);
            foreach ($users as $user) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user, "category" => $category_id->id]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    public function sendNotificationsToUserWithCategory($user_id, $identifier, $title, $message, $path = null) {
        try {
            $category = $this->cat_service->getCategoryByIdentifier($identifier);

            $title = filter_var($title, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            $message = filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

            // Push Notifications
            $clients = $this->client_service->getClientsByCategoryAndUser($category->id, $user_id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message, $path);
            }

            // Frontend
            $user_categories = $this->user_notifications_service->getCategoriesByUser($user_id);
            if (in_array($category->id, $user_categories)) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user_id, "category" => $category->id, "link" => $path]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    private function sendNotification(\App\Domain\Notifications\Clients\NotificationClient $entry, $title, $content, $path = null, $id = null) {

        $settings = $this->settings->getAppSettings()['push'];

        $subscription = Subscription::create([
                    'endpoint' => $entry->endpoint,
                    'publicKey' => $entry->publicKey,
                    'authToken' => $entry->authToken,
                    'contentEncoding' => $entry->contentEncoding
        ]);
        $auth = [
            'VAPID' => [
                'subject' => $settings["subject"],
                'publicKey' => $settings["publicKey"],
                'privateKey' => $settings["privateKey"]
            ]
        ];

        $data = [
            "url" => $this->helper->getBaseURL(),
            "path" => !is_null($path) ? $path : "/notifications/",
            "id" => !is_null($id) ? $id : -1
        ];

        $notification = [
            "title" => $title,
            "body" => $content,
            "data" => $data
        ];

        $this->logger->addInfo('PUSH', array("notification" => $notification));

        $webPush = new WebPush($auth);
        $options = [
            "TTL" => $settings["TTL"],
            "urgency" => $settings["urgency"]
        ];
        $res = $webPush->sendNotification($subscription, json_encode($notification), true, $options);

        foreach ($res as $report) {
            if ($report->isSuccess()) {
                $this->logger->addInfo('[PUSH] Message sent successfully', array("endpoint" => $report->getEndpoint()));
            } else {
                $data = [
                    "reason" => $report->getReason(),
                    "request" => $report->getRequest(),
                    "response" => $report->getResponse(),
                    "expired" => $report->isSubscriptionExpired()
                ];
                $this->logger->addError('[PUSH] Message failed to sent', $data);

                if ($report->isSubscriptionExpired()) {
                    $this->client_service->deleteClient($entry->id);
                    $this->logger->addError('[PUSH] Remove expired endpoint', array("id" => $entry->id, "endpoint" => $report->getEndpoint()));
                }
            }
        }
        //$this->logger->addError('[PUSH] Result', ["data" => $report]);

        return $report;
    }

    public function index() {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function notifyByCategory($requestData) {
        $category = array_key_exists("type", $requestData) ? filter_var($requestData["type"], FILTER_SANITIZE_STRING) : "";
        $title = array_key_exists("title", $requestData) ? filter_var($requestData["title"], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : "";
        $message = array_key_exists("message", $requestData) ? filter_var($requestData["message"], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : "";

        $this->sendNotificationsToUsersWithCategory($category, $title, $message);

        $response_data = ['status' => 'done'];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function manage() {
        $categories = $this->cat_service->getAllCategories();
        $user_categories = $this->getCategoriesOfCurrentUser();

        return new Payload(Payload::$RESULT_HTML, ["categories" => $categories, "user_categories" => $user_categories]);
    }

    public function sendTestNotification($entry_id, $data) {
        $title = array_key_exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : null;
        $message = array_key_exists('message', $data) ? filter_var($data['message'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) : null;

        $entry = $this->client_service->getEntry($entry_id);

        $result = $this->sendNotification($entry, $title, $message);

        if ($result) {
            return new Payload(Payload::$STATUS_NOTIFICATION_SUCCESS);
        }
        return new Payload(Payload::$STATUS_NOTIFICATION_FAILURE);
    }

}
