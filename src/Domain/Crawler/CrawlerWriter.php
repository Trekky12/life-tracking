<?php

namespace App\Domain\Crawler;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CrawlerWriter extends ObjectActivityWriter {

    private $crawler_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CrawlerMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        if ($this->crawler_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $lastAccess = [];
        if(!is_null($id)){
            $users_preSave = $this->mapper->getUsers($id);
            foreach($users_preSave as $user_id => $username){
                $lastAccess[$user_id] = $this->mapper->getLastAccess($id, $user_id);
            }
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        if(!empty($lastAccess)){
            $users_afterSave = $this->mapper->getUsers($id);
            foreach($users_afterSave as $user_id => $username){
                if(array_key_exists($user_id, $lastAccess)){
                    $this->mapper->setLastAccess($id, $user_id, $lastAccess[$user_id]);
                }
            }
        }

        return $payload;
    }

    public function getObjectViewRoute(): string {
        return 'crawlers_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "crawlers";
    }

}
