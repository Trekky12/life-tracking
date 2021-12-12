<?php

namespace App\Domain\Crawler\CrawlerHeader;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Crawler\CrawlerMapper;
use App\Domain\Crawler\CrawlerService;

class CrawlerHeaderRemover extends ObjectActivityRemover {

    private $service;
    private $crawler_service;
    private $crawler_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CrawlerHeaderService $service, CrawlerHeaderMapper $mapper, CrawlerService $crawler_service, CrawlerMapper $crawler_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
        $this->crawler_mapper = $crawler_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $crawler = $this->crawler_service->getFromHash($additionalData["crawler"]);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if(!$this->service->isChildOf($crawler->id, $id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        
        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->crawler_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'crawlers_headers_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        $crawler = $this->getParentMapper()->get($entry->getParentID());
        return [
            "crawler" => $crawler->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "crawlers";
    }

}
