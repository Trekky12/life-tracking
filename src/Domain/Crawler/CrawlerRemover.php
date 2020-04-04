<?php

namespace App\Domain\Crawler;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class CrawlerRemover extends ObjectActivityRemover {

    private $crawler_service;
    
    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CrawlerMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    public function delete($id, $additionalData = null): Payload {
        if ($this->crawler_service->isOwner($id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        return parent::delete($id, $additionalData);
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
