<?php

namespace App\Domain\Notifications\Clients;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;

class NotificationClientsService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Notifications\Clients\NotificationClient::class;
    protected $module = "notifications";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getClients() {
        return $this->mapper->getAll();
    }

    public function getClientsByCategory($category) {
        return $this->mapper->getClientsByCategory($category);
    }

    public function getClientsByCategoryAndUser($category, $user) {
        return $this->mapper->getClientsByCategoryAndUser($category, $user);
    }

    private function createNotificationClient($data) {
        $entry = new NotificationClient($data);
        $entry->ip = Utility::getIP();
        $entry->agent = Utility::getAgent();
        $entry->user = $this->current_user->getUser()->id;
        $entry->changedOn = date('Y-m-d H:i:s');

        return $entry;
    }

    public function createSubscription($data) {
        $client = $this->createNotificationClient($data);
        $this->mapper->insert($client);

        return $client;
    }

    public function updateSubscription($data) {
        $client = $this->createNotificationClient($data);
        $client->changedOn = date('Y-m-d H:i:s');

        try {
            $this->mapper->get($client->endpoint, true, 'endpoint');
            $this->mapper->update($client, "endpoint");
            return $client;
        } catch (\Exception $e) {
            // No Entry found so create one
            $this->logger->addWarning('Subscription not on server but on client', $client->endpoint);
            //$this->mapper->insert($client);
        }
        return null;
    }

    public function deleteSubscription($data) {
        $client = $this->createNotificationClient($data);
        $this->mapper->delete($client->endpoint, "endpoint");

        return $client;
    }

    public function deleteClient($id) {
        return $this->mapper->delete($id);
    }

    public function getCategoriesFromEndpoint($endpoint) {
        return $this->mapper->getCategoriesFromEndpoint($endpoint);
    }

    public function setCategoryOfEndpoint($endpoint, $category, $type) {
        $client = $this->mapper->getClientByEndpoint($endpoint);
        if ($type == 1) {
            $this->mapper->addCategory($client->id, $category);
        } else {
            $this->mapper->deleteCategory($client->id, $category);
        }
    }

}
