<?php

namespace App\Domain\Crawler\CrawlerDataset;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Crawler\CrawlerService;
use App\Application\Payload\Payload;

class CrawlerDatasetService extends Service {

    private $crawler_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerDatasetMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    public function deleteOldEntries($crawler_id) {

        $crawler = $this->crawler_service->getEntry($crawler_id);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $count = $this->mapper->deleteOldEntries($crawler_id);

        $response_data = ['status' => 'success', "crawler" => $crawler->getHash(), "count" => $count];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
