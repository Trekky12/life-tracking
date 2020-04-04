<?php

namespace App\Domain\Crawler\CrawlerDataset;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;

class CrawlerDatasetService extends GeneralService {

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerDatasetMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getCountFromCrawler($crawler_id, $from, $to, $filter, $searchQuery = "%") {
        return $this->mapper->getCountFromCrawler($crawler_id, $from, $to, $filter, $searchQuery);
    }

    public function getDataFromCrawler($crawler_id, $from, $to, $filter, $sortColumn, $sortDirection, $limit = 20, $start = 0, $searchQuery = "%") {
        return $this->mapper->getDataFromCrawler($crawler_id, $from, $to, $filter, $sortColumn, $sortDirection, $limit, $start, $searchQuery);
    }

}
