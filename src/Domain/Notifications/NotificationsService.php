<?php

namespace App\Domain\Notifications;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationsService extends \App\Domain\Service {

    protected $module = "notifications";
    private $user_notifications_service;
    private $cat_service;
    private $client_service;
    private $helper;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            Users\NotificationUsersService $user_notifications_service,
            Categories\NotificationCategoryService $cat_service,
            Clients\NotificationClientsService $client_service,
            Helper $helper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

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

    public function getNotifications($limit = 10, $offset = 0) {
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

        return $result;
    }

    public function sendNotificationsToUsersWithCategory($identifier, $title, $message) {
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

            $title = filter_var($title, FILTER_SANITIZE_STRING);
            $message = filter_var($message, FILTER_SANITIZE_STRING);

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

    public function sendNotification(\App\Domain\Notifications\Clients\NotificationClient $entry, $title, $content, $path = null, $id = null) {

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

}
