<?php

namespace App\Notifications\Clients;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Notifications\Clients\NotificationClient';
        $this->index_route = 'notifications';

        $this->mapper = new Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $users = $this->user_mapper->getAll();
        return $this->ci->view->render($response, 'notifications/clients/index.twig', ['list' => $list, 'users' => $users]);
    }

    public function subscribe(Request $request, Response $response) {

        //$data = json_decode($request->getBody(), true);
        $data = $request->getParsedBody();

        $logger = $this->ci->get('logger');

        $entry = new NotificationClient($data);
        $entry->ip = $this->ci->get('helper')->getIP();
        $entry->agent = $this->ci->get('helper')->getAgent();
        $entry->user = $this->ci->get('helper')->getUser()->id;
        $entry->changedOn = date('Y-m-d H:i:s');
        
        $result = array('status' => 'error');

        if ($request->isPost()) {
            $logger->addInfo('Subscription insert', $entry->get_fields());
            $this->mapper->insert($entry);
            $result['status'] = 'success';
        }
        if ($request->isPut()) {
            $entry->changedOn = date('Y-m-d H:i:s');

            try {
                $this->mapper->get($entry->endpoint, true, 'endpoint');
                $this->mapper->update($entry, "endpoint");
                $result['status'] = 'success';
            } catch (\Exception $e) {
                // No Entry found so create one
                //$logger->addWarning('Subscription not on server but on client', $entry->get_fields());
                //$this->mapper->insert($entry);
            }
        }
        if ($request->isDelete()) {
            $logger->addInfo('Subscription delete', $entry->get_fields());
            $this->mapper->delete($entry->endpoint, "endpoint");
            $result['status'] = 'success';
        }

        return $response->withJSON($result);
    }

    public function getCategoriesFromEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["data" => [], "status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $result["data"] = $this->mapper->getCategoriesByEndpoint($endpoint);

        return $response->withJson($result);
    }

    public function setCategoryOfEndpoint(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $result = ["status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $category = array_key_exists('category', $data) ? intval(filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        
        $client = $this->mapper->getClientByEndpoint($endpoint);        
        if($type == 1){
            $this->mapper->addCategory($client->id, $category);
        }else{
            $this->mapper->deleteCategory($client->id, $category);
        }

        return $response->withJson($result);
    }

}
