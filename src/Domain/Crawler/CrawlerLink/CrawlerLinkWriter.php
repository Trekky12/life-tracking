<?php

namespace App\Domain\Crawler\CrawlerLink;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Crawler\CrawlerMapper;
use App\Domain\Crawler\CrawlerService;

class CrawlerLinkWriter extends ObjectActivityWriter {

    private $service;
    private $crawler_service;
    private $crawler_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, CrawlerLinkService $service, CrawlerLinkMapper $mapper, CrawlerService $crawler_service, CrawlerMapper $crawler_mapper) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
        $this->crawler_mapper = $crawler_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $crawler = $this->crawler_service->getFromHash($additionalData["crawler"]);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if(!$this->service->isChildOf($crawler->id, $id)){
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['crawler'] = $crawler->id;

        return parent::save($id, $data, $additionalData);
    }

    public function getParentMapper() {
        return $this->crawler_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'crawlers_links_edit';
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
