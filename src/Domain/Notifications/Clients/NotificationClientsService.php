<?php

namespace App\Domain\Notifications\Clients;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;
use App\Application\Payload\Payload;
use App\Domain\User\UserService;

class NotificationClientsService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, NotificationClientsMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function getClients() {
        return $this->mapper->getAll();
    }

    public function getClientsByCategory($category) {
        return $this->mapper->getClientsByCategory($category);
    }

    public function getClientsByCategoryAndUser($category, $user, $object_id) {
        return $this->mapper->getClientsByCategoryAndUser($category, $user, $object_id);
    }
    
    public function getClientByUserAndType($type) {
        $user = $this->current_user->getUser();
        return $this->mapper->getClientByUserAndType($user->id, $type);
    }

    private function createNotificationClient($data) {
        $entry = new NotificationClient($data);
        $entry->ip = Utility::getIP();
        $entry->agent = Utility::getAgent();
        $entry->user = $this->current_user->getUser()->id;
        $entry->changedOn = date('Y-m-d H:i:s');

        return $entry;
    }

    private function createSubscription($data) {
        $client = $this->createNotificationClient($data);
        $this->mapper->insert($client);

        return $client;
    }

    private function updateSubscription($data) {
        $client = $this->createNotificationClient($data);
        $client->changedOn = date('Y-m-d H:i:s');

        try {
            $this->mapper->get($client->endpoint, true, 'endpoint');
            $this->mapper->update($client, "endpoint");
            return $client;
        } catch (\Exception $e) {
            // No Entry found so create one
            $this->logger->warning('Subscription not on server but on client', $client->endpoint);
            //$this->mapper->insert($client);
        }
        return null;
    }

    private function deleteSubscription($data) {
        $client = $this->createNotificationClient($data);
        $this->mapper->delete($client->endpoint, "endpoint");

        return $client;
    }

    public function deleteClient($id) {
        return $this->mapper->delete($id);
    }

    public function getCategoriesFromEndpoint($data) {
        $result = ["data" => [], "status" => "success"];
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $result["data"] = $this->mapper->getCategoriesFromEndpoint($endpoint);
        
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function setCategoryOfEndpoint($data) {
        $endpoint = array_key_exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $cat = array_key_exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_STRING) : "";
        $type = array_key_exists('type', $data) ? intval(filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        
        $category = intval($cat);
        $object_id = null;
        if(strpos($cat, "_")){
            $cat_and_id = explode("_", $cat);
            $category = intval($cat_and_id[0]);
            $object_id = intval($cat_and_id[1]);
        }

        $client = $this->mapper->getClientByEndpoint($endpoint);
        if ($type == 1) {
            $this->mapper->addCategory($client->id, $category, $object_id);
        } else {
            $this->mapper->deleteCategory($client->id, $category, $object_id);
        }
        $result = ["status" => "success"];
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function create($data) {
        $result = ['status' => 'error'];
        $entry = $this->createSubscription($data);
        $this->logger->info('Subscription insert', $entry->get_fields());

        $result['status'] = 'success';
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function update($data) {
        $result = ['status' => 'error'];
        $client = $this->updateSubscription($data);

        if ($client) {
            $result['status'] = 'success';
        }
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function delete($data) {
        $result = ['status' => 'error'];
        $entry = $this->deleteSubscription($data);
        $this->logger->info('Subscription delete', $entry->get_fields());

        $result['status'] = 'success';
        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function index() {
        $list = $this->getClients();
        $users = $this->user_service->getAll();
        return new Payload(Payload::$RESULT_HTML, ['list' => $list, 'users' => $users]);
    }

    public function showTest($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
