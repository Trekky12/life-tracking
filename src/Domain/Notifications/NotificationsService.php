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
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Board\BoardService;
use App\Domain\Main\Utility\Utility;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Reminder\ReminderService;

class NotificationsService extends Service {

    private $translation;
    private $settings;
    private $user_notifications_service;
    private $cat_service;
    private $client_service;
    private $helper;
    private $splitbill_group_service;
    private $boards_service;
    private $mail_users_mapper;
    private $mail_cat_mapper;
    private $timesheet_project_service;
    private $reminder_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Translator $translation,
        Settings $settings,
        NotificationsMapper $mapper,
        Users\NotificationUsersService $user_notifications_service,
        Categories\NotificationCategoryService $cat_service,
        Clients\NotificationClientsService $client_service,
        Helper $helper,
        SplitbillGroupService $splitbill_group_service,
        BoardService $boards_service,
        MailNotificationUsersMapper $mail_users_mapper,
        MailNotificationCategoryMapper $mail_cat_mapper,
        ProjectService $timesheet_project_service,
        ReminderService $reminder_service
    ) {
        parent::__construct($logger, $user);
        $this->translation = $translation;
        $this->settings = $settings;
        $this->mapper = $mapper;
        $this->user_notifications_service = $user_notifications_service;
        $this->cat_service = $cat_service;
        $this->client_service = $client_service;
        $this->helper = $helper;
        $this->splitbill_group_service = $splitbill_group_service;
        $this->boards_service = $boards_service;
        $this->timesheet_project_service = $timesheet_project_service;
        $this->reminder_service = $reminder_service;

        $this->mail_users_mapper = $mail_users_mapper;
        $this->mail_cat_mapper = $mail_cat_mapper;

        //var_dump(\Minishlink\WebPush\VAPID::createVapidKeys());
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

    public function getUnreadNotifications() {
        $result = [];
        $result["unseen"] = $this->getUnreadNotificationsCountByUser();

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function getNotifications($data) {

        //$endpoint = array_key_exists('endpoint', $data) ? Utility::filter_string_polyfill($data['endpoint']) : null;
        $limit = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 5;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $user = $this->current_user->getUser();
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);

        $categories = $this->cat_service->getAllCategories();
        array_map(function ($cat) {
            if ($cat->isInternal() && !$cat->hasReminder()) {
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
            $notification_ids = array_map(function ($el) {
                return $el->id;
            }, $notifications);
            $this->mapper->markAsSeen($notification_ids);
        }

        $result["unseen"] = $this->mapper->getUnreadNotificationsCountByUser($user->id);

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function sendNotificationsToUsersWithCategory($identifier, $title, $message, $path = null, $object_id = null) {
        try {
            $category_id = $this->cat_service->getCategoryByIdentifier($identifier);

            // Push Notifications
            $clients = $this->client_service->getClientsByCategory($category_id->id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message, $path);
            }

            // Save notification for the users (frontend)
            $users = $this->user_notifications_service->getUsersByCategory($category_id->id, $object_id);
            foreach ($users as $user) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user, "category" => $category_id->id, "link" => $path]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    public function sendNotificationsToUserWithCategory($user_id, $identifier, $title, $message, $path = null, $object_id = null) {
        try {
            $category = $this->cat_service->getCategoryByIdentifier($identifier);

            $title = filter_var($title, FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES);
            $message = filter_var($message, FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES);

            // Push Notifications
            $clients = $this->client_service->getClientsByCategoryAndUser($category->id, $user_id, $object_id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message, $path);
            }

            // Frontend
            $user_has_category = $this->user_notifications_service->doesUserHaveCategory($category->id, $user_id, $object_id);
            if ($user_has_category) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user_id, "category" => $category->id, "link" => $path]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    public function sendNotificationsToUsersWithReminder($reminder, $title, $message, $path = null, $object_id = null) {
        try {
            $category_id = $this->cat_service->getCategoryByReminder($reminder);

            // Push Notifications
            $clients = $this->client_service->getClientsByCategory($category_id->id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message, $path);
            }

            // Save notification for the users (frontend)
            $users = $this->user_notifications_service->getUsersByCategory($category_id->id, $object_id);
            foreach ($users as $user) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user, "category" => $category_id->id, "link" => $path]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

    private function sendNotification(\App\Domain\Notifications\Clients\NotificationClient $entry, $title, $content, $path = null, $id = null) {

        $data = [
            "url" => $this->helper->getBaseURL(),
            //"path" => !is_null($path) ? $path : "/notifications/",
            "path" => "/notifications/",
            "id" => !is_null($id) ? $id : -1
        ];

        if ($entry->type == "ifttt") {
            $ifttt_data = json_encode(["value1" => $title, "value2" => $content, "value3" => sprintf("%s%s", $data["url"], $data["path"])]);
            list($status, $result) = $this->helper->request($entry->endpoint, 'POST', $ifttt_data, array('Content-Type: application/json'));

            $this->logger->info('PUSH IFTTT', array("data" => $ifttt_data, "status" => $status, "result" => $result));

            return $status == 200;
        }

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

        $unseen = $this->mapper->getUnreadNotificationsCountByUser($entry->user);

        $notification = [
            "title" => strip_tags($title),
            "body" => strip_tags($content),
            "data" => $data,
            "unseen" => $unseen
        ];

        $this->logger->info('PUSH', array("notification" => $notification));

        $webPush = new WebPush($auth);
        $options = [
            "TTL" => $settings["TTL"],
            "urgency" => $settings["urgency"]
        ];
        $report = $webPush->sendOneNotification($subscription, json_encode($notification), $options);

        if ($report->isSuccess()) {
            $this->logger->info('[PUSH] Message sent successfully', array("endpoint" => $report->getEndpoint()));
        } else {
            $data = [
                "reason" => $report->getReason(),
                "request" => $report->getRequest(),
                "response" => $report->getResponse(),
                "expired" => $report->isSubscriptionExpired()
            ];
            $this->logger->error('[PUSH] Message failed to sent', $data);

            if ($report->isSubscriptionExpired()) {
                $this->client_service->deleteClient($entry->id);
                $this->logger->error('[PUSH] Remove expired endpoint', array("id" => $entry->id, "endpoint" => $report->getEndpoint()));
            }
        }
        //$this->logger->error('[PUSH] Result', ["data" => $report]);

        return $report;
    }

    public function index() {
        return new Payload(Payload::$RESULT_HTML);
    }

    public function notifyByCategory($params) {
        $category = array_key_exists("category", $params) ? Utility::filter_string_polyfill($params["category"]) : "";
        $title = array_key_exists("title", $params) ? filter_var($params["title"], FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES) : "";
        $message = array_key_exists("message", $params) ? html_entity_decode($params['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : "";

        $this->logger->debug('[PUSH] Query', ["params" => $params]);

        $this->sendNotificationsToUsersWithCategory($category, $title, $message);

        $response_data = ['status' => 'done'];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function manage() {
        $notification_categories = $this->cat_service->getUserCategories();
        $notification_categories_internal = array_filter($notification_categories, function ($cat) {
            return $cat->isInternal() && !$cat->hasReminder();
        });

        $notification_categories_individual = array_filter($notification_categories, function ($cat) {
            return !$cat->isInternal() && !$cat->hasReminder();
        });

        $notification_user_categories = $this->getCategoriesOfCurrentUser();

        $ifttt_clients = $this->client_service->getClientsByUserAndType("ifttt");

        $splitbill_user_groups = $this->splitbill_group_service->getUserElements();
        $splitbill_all_groups = $this->splitbill_group_service->getAll();

        $boards_user_boards = $this->boards_service->getUserElements();
        $boards_all_boards = $this->boards_service->getAll();

        $timesheet_user_groups = $this->timesheet_project_service->getUserElements();
        $timesheet_all_groups = $this->timesheet_project_service->getAll();
        $timesheet_reminders = $this->reminder_service->getRemindersByProject();

        $mail_categories = $this->mail_cat_mapper->getAll();

        $user = $this->current_user->getUser();
        $mail_user_categories = $this->mail_users_mapper->getCategoriesByUser($user->id);

        return new Payload(Payload::$RESULT_HTML, [
            "mail" => [
                "categories" => $mail_categories,
                "user_categories" => $mail_user_categories,
            ],
            "notifications" => [
                "categories" => [
                    "internal" => $notification_categories_internal,
                    "individual" => $notification_categories_individual
                ],
                "user_categories" => $notification_user_categories,
                "ifttt_clients" => $ifttt_clients,
            ],
            "splitbill" => [
                "groups" => $splitbill_all_groups,
                "user_groups" => $splitbill_user_groups
            ],
            "boards" => [
                "boards" => $boards_all_boards,
                "user_boards" => $boards_user_boards
            ],
            "timesheets" => [
                "projects" => $timesheet_all_groups,
                "user_projects" => $timesheet_user_groups,
                "reminders" => $timesheet_reminders
            ]
        ]);
    }

    public function sendTestNotification($entry_id, $data) {
        $title = array_key_exists('title', $data) ? filter_var($data['title'], FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES) : null;
        $message = array_key_exists('message', $data) ? filter_var($data['message'], FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES) : null;

        $entry = $this->client_service->getEntry($entry_id);

        $result = $this->sendNotification($entry, $title, $message);

        $notification = new Notification(["title" => $title, "message" => $message, "user" => $entry->user]);
        $id = $this->mapper->insert($notification);

        if ($result) {
            return new Payload(Payload::$STATUS_NOTIFICATION_SUCCESS);
        }
        return new Payload(Payload::$STATUS_NOTIFICATION_FAILURE);
    }

    public function setMailNotificationCategoryForUser($data) {

        $cat = array_key_exists('category', $data) ? Utility::filter_string_polyfill($data['category']) : "";
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $category = intval($cat);
        $object_id = null;
        if (strpos($cat, "_")) {
            $cat_and_id = explode("_", $cat);
            $category = intval($cat_and_id[0]);
            $object_id = intval($cat_and_id[1]);
        }

        $user = $this->current_user->getUser();

        if ($type == 1) {
            $this->mail_users_mapper->addCategory($user->id, $category, $object_id);
        } else {
            $this->mail_users_mapper->deleteCategory($user->id, $category, $object_id);
        }
        $result = ["status" => "success"];
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function sendMailNotificationToUserWithCategory($user, $identifier, $template, $subject, $body, $object_id = null) {
        try {
            $category = $this->mail_cat_mapper->getCategoryByIdentifier($identifier);

            $user_has_category = $this->mail_users_mapper->doesUserHaveCategory($category->id, $user->id, $object_id);
            if ($user_has_category) {
                $this->helper->send_mail($template, $user->mail, $subject, $body);
            }
        } catch (\Exception $e) {
            $this->logger->error("Error with Mail", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }
}
