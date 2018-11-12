<?php

namespace App\User\Notifications;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\User\Notifications\Notification';
        $this->index_route = 'notifications';

        $this->mapper = new \App\User\Notifications\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->ci->view->render($response, 'notifications/index.twig', ['list' => $list, 'users' => $users]);
    }

    public function testNotification(Request $request, Response $response) {
        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id, true);
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

        return $this->ci->view->render($response, 'notifications/test.twig', ['entry' => $entry]);
    }

    public function manage(Request $request, Response $response) {
        return $this->ci->view->render($response, 'notifications/manage.twig', []);
    }

    public function subscribe(Request $request, Response $response) {

        $data = json_decode($request->getBody(), true);
        
        

        $logger = $this->ci->get('logger');

        $entry = new Notification($data);
        $entry->ip = $this->ci->get('helper')->getIP();
        $entry->agent = $this->ci->get('helper')->getAgent();
        $entry->user = $this->ci->get('helper')->getUser()->id;
        $entry->changedOn = date('Y-m-d G:i:s');

        if ($request->isPost()) {
            $logger->addInfo('Subscription insert', $entry->get_fields());
            $this->mapper->insert($entry);
        }
        if ($request->isPut()) {
            $entry->changedOn = date('Y-m-d G:i:s');

            try {
                $this->mapper->get($entry->endpoint, true, 'endpoint');
            } catch (\Exception $e) {
                // No Entry found so create one
                $logger->addWarning('Subscription not on server but on client', $entry->get_fields());
                $this->mapper->insert($entry);
            }
            $this->mapper->update($entry, "endpoint");
        }
        if ($request->isDelete()) {
            $logger->addInfo('Subscription delete', $entry->get_fields());
            $this->mapper->delete($entry->endpoint, "endpoint");
        }

        return $response->withJSON(array('status' => 'success'));
    }

    private function sendNotification(\App\User\Notifications\Notification $entry, $title, $content) {

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

        $data = '{"title":"' . $title . '", "body":"' . $content . '", "data":"' . $this->ci->get('helper')->getPath() . '"}';

        $logger->addInfo('PUSH', array("data" => $data));

        $webPush = new WebPush($auth);
        $res = $webPush->sendNotification(
                $subscription, $data, true
        );
        return $res;
    }

}
