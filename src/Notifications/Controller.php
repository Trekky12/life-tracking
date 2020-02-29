<?php

namespace App\Notifications;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class Controller extends \App\Base\Controller {

    protected $index_route = 'notifications_clients';
    protected $module = "notifications";
    private $category_mapper;
    private $client_mapper;
    private $user_notifications_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->category_mapper = new Categories\Mapper($this->db, $this->translation, $user);
        $this->client_mapper = new Clients\Mapper($this->db, $this->translation, $user);
        $this->user_notifications_mapper = new Users\Mapper($this->db, $this->translation, $user);
    }

    public function manage(Request $request, Response $response) {
        $categories = $this->category_mapper->getAll();

        $user = $this->user_helper->getUser();
        $user_categories = $this->user_notifications_mapper->getCategoriesByUser($user->id);

        return $this->twig->render($response, 'notifications/manage.twig', ["categories" => $categories, "user_categories" => $user_categories]);
    }

    public function overview(Request $request, Response $response) {
        $categories = $this->category_mapper->getAll();

        $user = $this->user_helper->getUser();
        $limit = 10;
        $offset = 0;
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);

        return $this->twig->render($response, 'notifications/overview.twig', ["categories" => $categories, "notifications" => $notifications]);
    }

    public function notifyByCategory(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $category = array_key_exists("type", $requestData) ? filter_var($requestData["type"], FILTER_SANITIZE_STRING) : "";
        $title = array_key_exists("title", $requestData) ? filter_var($requestData["title"], FILTER_SANITIZE_STRING) : "";
        $message = array_key_exists("message", $requestData) ? filter_var($requestData["message"], FILTER_SANITIZE_STRING) : "";

        $this->sendNotificationsToUsersWithCategory($category, $title, $message);

        $response_data = ['status' => 'done'];
        return $response->withJson($response_data);
    }

    public function testNotification(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->client_mapper->get($entry_id, true);
        }

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $title = array_key_exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;
            $message = array_key_exists('message', $data) ? filter_var($data['message'], FILTER_SANITIZE_STRING) : null;

            $result = $this->sendNotification($entry, $title, $message);

            if ($result) {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_SUCCESS"));
                $this->flash->addMessage('message_type', 'success');
            } else {
                $this->flash->addMessage('message', $this->translation->getTranslatedString("NOTIFICATION_SEND_FAILURE"));
                $this->flash->addMessage('message_type', 'danger');
            }
            return $response->withRedirect($this->router->urlFor($this->index_route), 301);
        }

        return $this->twig->render($response, 'notifications/clients/test.twig', ['entry' => $entry]);
    }

    private function sendNotification(\App\Notifications\Clients\NotificationClient $entry, $title, $content, $path = null, $id = null) {

        $settings = $this->settings['app']['push'];

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
                    $this->client_mapper->delete($entry->id);
                    $this->logger->addError('[PUSH] Remove expired endpoint', array("id" => $entry->id, "endpoint" => $report->getEndpoint()));
                }
            }
        }
        //$this->logger->addError('[PUSH] Result', ["data" => $report]);

        return $report;
    }

    public function getNotificationsByUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $limit = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 5;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;

        //$client = $this->client_mapper->getClientByEndpoint($endpoint);
        $user = $this->user_helper->getUser();
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);

        $categories = $this->category_mapper->getAll();
        array_map(function($cat) {
            if ($cat->isInternal()) {
                $cat->name = $this->translation->getTranslatedString($cat->name);
            }
            return $cat;
        }, $categories);

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

        return $response->withJson($result);
    }

    /* public function getUnreadNotificationsByUser(Request $request, Response $response) {
      //$data = $request->getParsedBody();

      $result = ["data" => [], "status" => "success"];
      //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
      //$client = $this->client_mapper->getClientByEndpoint($endpoint);
      $user = $this->user_helper->getUser();
      $result["data"] = $this->mapper->getUnreadNotificationsCountByUser($user->id);

      return $response->withJson($result);
      } */

    public function sendNotificationsToUsersWithCategory($identifier, $title, $message) {
        try {
            $category_id = $this->category_mapper->getCategoryByIdentifier($identifier);

            // Push Notifications
            $clients = $this->client_mapper->getClientsByCategory($category_id->id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message);
            }

            // Save notification for the users (frontend)
            $users = $this->user_notifications_mapper->getUsersByCategory($category_id->id);
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
            $category = $this->category_mapper->getCategoryByIdentifier($identifier);

            $title = filter_var($title, FILTER_SANITIZE_STRING);
            $message = filter_var($message, FILTER_SANITIZE_STRING);

            // Push Notifications
            $clients = $this->client_mapper->getClientsByCategoryAndUser($category->id, $user_id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message, $path);
            }

            // Frontend
            $user_categories = $this->user_notifications_mapper->getCategoriesByUser($user_id);
            if (in_array($category->id, $user_categories)) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user_id, "category" => $category->id, "link" => $path]);
                $id = $this->mapper->insert($notification);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }
    }

}
