<?php

namespace App\Domain\Crawler\CrawlerHeader;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Crawler\CrawlerService;
use App\Domain\Crawler\Crawler;

class CrawlerHeaderService extends Service {

    private $crawler_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerHeaderMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    private function cloneHeaders(Crawler $target, Crawler $destination) {
        $clone_elements = $this->mapper->getFromCrawler($target->id);
        foreach ($clone_elements as &$clone) {
            $fromID = $clone->id;
            $clone->crawler = $destination->id;
            $clone->id = null;
            $id = $this->mapper->insert($clone);

            $this->logger->notice("Duplicate crawler headline", array("from" => $target->id, "to" => $destination->id, "fromID" => $fromID, "toID" => $id));
        }
    }

    private function getSortOptions() {
        return [
            null => 'NO_INITIAL_SORTING',
            "asc" => 'ASC',
            "desc" => 'DESC'
        ];
    }

    // @see https://dev.mysql.com/doc/refman/8.0/en/cast-functions.html#function_cast
    private function getCastOptions() {
        return [
            null => 'CAST_NONE',
            "BINARY" => 'CAST_BINARY',
            "CHAR" => 'CAST_CHAR',
            "DATE" => 'CAST_DATE',
            "DATETIME" => 'CAST_DATETIME',
            "DECIMAL" => 'CAST_DECIMAL',
            "SIGNED" => 'CAST_SIGNED',
            "TIME" => 'CAST_TIME',
            "UNSIGNED" => 'CAST_UNSIGNED',
        ];
    }

    public function index($hash) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $headers = $this->mapper->getFromCrawler($crawler->id);

        return new Payload(Payload::$RESULT_HTML, ['headers' => $headers, "crawler" => $crawler]);
    }

    public function edit($hash, $entry_id) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "crawler" => $crawler,
            "sortOptions" => $this->getSortOptions(),
            "castOptions" => $this->getCastOptions()
        ]);
    }

    public function clonePage($hash) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $crawlers = $this->crawler_service->getCrawlersOfUser();

        return new Payload(Payload::$RESULT_HTML, [
            'crawler' => $crawler,
            'crawlers' => $crawlers
        ]);
    }

    public function clone($destination_hash, $target_id) {

        $crawler_destination = $this->crawler_service->getFromHash($destination_hash);
        $crawler_target = $this->crawler_service->getEntry($target_id);

        if (!$this->crawler_service->isOwner($crawler_destination->id) && !$this->crawler_service->isOwner($crawler_target->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $this->cloneHeaders($crawler_target, $crawler_destination);

        $response_data = ['status' => 'success'];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
