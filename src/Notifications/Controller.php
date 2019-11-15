<?php

namespace App\Notifications;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class Controller extends \App\Base\Controller {

    private $category_mapper;
    private $client_mapper;

    public function init() {
        $this->index_route = 'notifications_clients';
        $this->mapper = new \App\Notifications\Mapper($this->ci);
        $this->category_mapper = new \App\Notifications\Categories\Mapper($this->ci);
        $this->client_mapper = new \App\Notifications\Clients\Mapper($this->ci);
        $this->user_notifications_mapper = new \App\Notifications\Users\Mapper($this->ci);
    }

    public function manage(Request $request, Response $response) {
        $categories = $this->category_mapper->getAll();
        
        $user = $this->ci->get('helper')->getUser();
        $user_categories = $this->user_notifications_mapper->getCategoriesByUser($user->id);

        return $this->ci->view->render($response, 'notifications/manage.twig', ["categories" => $categories, "user_categories" => $user_categories]);
    }

    public function overview(Request $request, Response $response) {
        $categories = $this->category_mapper->getAll();
        
        $user = $this->ci->get('helper')->getUser();
        $limit = 10;
        $offset = 0;
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);
        
        return $this->ci->view->render($response, 'notifications/overview.twig', ["categories" => $categories, "notifications" => $notifications]);
    }

    public function notifyByCategory(Request $request, Response $response) {

        $logger = $this->ci->get('logger');

        $requestData = $request->getQueryParams();

        $category = array_key_exists("type", $requestData) ? filter_var($requestData["type"], FILTER_SANITIZE_STRING) : "";
        $title = array_key_exists("title", $requestData) ? filter_var($requestData["title"], FILTER_SANITIZE_STRING) : "";
        $message = array_key_exists("message", $requestData) ? filter_var($requestData["message"], FILTER_SANITIZE_STRING) : "";

        try {
            $category_id = $this->category_mapper->getCategoryByIdentifier($category);
            
            // Push Notifications
            $clients = $this->client_mapper->getClientsByCategory($category_id->id);
            foreach ($clients as $client) {
                $res = $this->sendNotification($client, $title, $message);
            }
            
            // Save notification for the users (frontend)
            $users = $this->user_notifications_mapper->getUsersByCategory($category_id->id);
            foreach ($users as $user) {
                $notification = new Notification(["title" => $title, "message" => $message, "user" => $user, "category" => $category_id->id]);
                $id = $this->mapper->insert($notification, false);
            }
            
        } catch (\Exception $e) {
            $logger->addError("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }

        return $response->withJson(['status' => 'done']);
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
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("NOTIFICATION_SEND_SUCCESS"));
                $this->ci->get('flash')->addMessage('message_type', 'success');
            } else {
                $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("NOTIFICATION_SEND_FAILURE"));
                $this->ci->get('flash')->addMessage('message_type', 'danger');
            }
            return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
        }

        return $this->ci->view->render($response, 'notifications/clients/test.twig', ['entry' => $entry]);
    }

    private function sendNotification(\App\Notifications\Clients\NotificationClient $entry, $title, $content, $id = null) {

        $settings = $this->ci->get('settings')['app']['push'];
        $logger = $this->ci->get('logger');

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
            "url" => $this->ci->get('helper')->getPath(),
            "path" => "/notifications/",
            "id" => !is_null($id) ? $id : -1
        ];

        $notification = [
            "title" => $title,
            "body" => $content,
            "data" => $data
        ];

        $logger->addInfo('PUSH', array("notification" => $notification));

        $webPush = new WebPush($auth);
        $options = [
            "TTL" => $settings["TTL"],
            "urgency" => $settings["urgency"]
        ];
        $res = $webPush->sendNotification($subscription, json_encode($notification), true, $options);

        foreach ($res as $report) {
            if ($report->isSuccess()) {
                $logger->addInfo('[PUSH] Message sent successfully', array("endpoint" => $report->getEndpoint()));
            } else {
                $data = [
                    "reason" => $report->getReason(),
                    "request" => $report->getRequest(),
                    "response" => $report->getResponse(),
                    "expired" => $report->isSubscriptionExpired()
                ];
                $logger->addError('[PUSH] Message failed to sent', $data);

                if ($report->isSubscriptionExpired()) {
                    $this->client_mapper->delete($entry->id);
                    $logger->addError('[PUSH] Remove expired endpoint', array("id" => $entry->id, "endpoint" => $report->getEndpoint()));
                }
            }
        }
        //$logger->addError('[PUSH] Result', ["data" => $report]);

        return $report;
    }

    public function getNotificationsByUser(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $limit = array_key_exists('count', $data) ? filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT) : 5;
        $offset = array_key_exists('start', $data) ? filter_var($data['start'], FILTER_SANITIZE_NUMBER_INT) : 0;

        //$client = $this->client_mapper->getClientByEndpoint($endpoint);
        $user = $this->ci->get('helper')->getUser();
        $notifications = $this->mapper->getNotificationsByUser($user->id, $limit, $offset);
        $result["data"] = $notifications;
        $result["count"] = $this->mapper->getNotificationsCountByUser($user->id);
        $result["categories"] = $this->category_mapper->getAll();

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

    public function getUnreadNotificationsByUser(Request $request, Response $response) {
        //$data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        //$endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;

        //$client = $this->client_mapper->getClientByEndpoint($endpoint);
        $user = $this->ci->get('helper')->getUser();
        $result["data"] = $this->mapper->getUnreadNotificationsCountByUser($user->id);

        return $response->withJson($result);
    }

}
