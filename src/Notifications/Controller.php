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
        $this->category_mapper = new \App\Notifications\Categories\Mapper($this->ci);
        $this->client_mapper = new \App\Notifications\Clients\Mapper($this->ci);
    }

    public function manage(Request $request, Response $response) {
        $categories = $this->category_mapper->getAll();
        return $this->ci->view->render($response, 'notifications/manage.twig', ["categories" => $categories]);
    }

    public function notifyByCategory(Request $request, Response $response) {
        
        $logger = $this->ci->get('logger');
        
        $requestData = $request->getQueryParams();

        $category = array_key_exists("type", $requestData) ? filter_var($requestData["type"], FILTER_SANITIZE_STRING) : "";
        $title = array_key_exists("title", $requestData) ? filter_var($requestData["title"], FILTER_SANITIZE_STRING) : "";
        $message = array_key_exists("message", $requestData) ? filter_var($requestData["message"], FILTER_SANITIZE_STRING) : "";

        $clients = [];
        try {
            $category_id = $this->category_mapper->getCategoryByIdentifier($category);
            $clients = $this->client_mapper->getClientsByCategory($category_id->id);
            
            foreach($clients as $client){
                $res = $this->sendNotification($client, $title, $message);
            }
        } catch (\Exception $e) {
            $logger->addError("Error with Notifications", array("error" => $e->getMessage(), "code" => $e->getCode()));
        }

        return $response->withJson(['clients' => $clients]);
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
            $content = array_key_exists('content', $data) ? filter_var($data['content'], FILTER_SANITIZE_STRING) : null;

            $result = $this->sendNotification($entry, $title, $content);
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

    private function sendNotification(\App\Notifications\Clients\NotificationClient $entry, $title, $content) {

        $settings = $this->ci->get('settings')['app']['push'];
        $logger = $this->ci->get('logger');

        $subscription = Subscription::create([
                    'endpoint' => $entry->endpoint,
                    'publicKey' => $entry->publicKey,
                    'authToken' => $entry->authToken,
                    'contentEncoding' => $entry->contentEncoding
        ]);
        $auth = array(
            'VAPID' => array(
                'subject' => $settings["subject"],
                'publicKey' => $settings["publicKey"],
                'privateKey' => $settings["privateKey"]
            )
        );

        $data = array("title" => $title, "body" => $content, "data" => array("url" => $this->ci->get('helper')->getPath(), "path" => "/notifications/"));

        $logger->addInfo('PUSH', array("data" => $data));

        $webPush = new WebPush($auth);
        $res = $webPush->sendNotification(
                $subscription, json_encode($data), true
        );

        $logger->addInfo('PUSH RESULT', array("data" => $res));
        return $res;
    }

}
