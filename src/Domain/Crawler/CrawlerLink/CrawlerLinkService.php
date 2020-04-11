<?php

namespace App\Domain\Crawler\CrawlerLink;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Crawler\CrawlerService;

class CrawlerLinkService extends Service {

    private $crawler_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerLinkMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    public function index($hash) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $links = $this->mapper->getFromCrawler($crawler->id);

        return new Payload(Payload::$RESULT_HTML, ['links' => $links, "crawler" => $crawler]);
    }

    public function edit($hash, $entry_id) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        $links = $this->mapper->getFromCrawler($crawler->id, 'position');

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'crawler' => $crawler, 'links' => $links]);
    }

}
