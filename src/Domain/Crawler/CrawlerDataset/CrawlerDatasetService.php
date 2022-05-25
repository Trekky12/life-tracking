<?php

namespace App\Domain\Crawler\CrawlerDataset;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Crawler\CrawlerService;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderMapper;
use App\Application\Payload\Payload;

class CrawlerDatasetService extends Service {

    private $crawler_service;
    private $header_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerDatasetMapper $mapper, CrawlerService $crawler_service, CrawlerHeaderMapper $header_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
        $this->header_mapper = $header_mapper;
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

    public function setSave($hash, $data) {
        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = ['status' => 'error'];

        if (array_key_exists("state", $data) && in_array($data["state"], array(0, 1)) && array_key_exists("dataset", $data)) {

            $dataset = intval($data["dataset"]);
            $state = intval($data["state"]);

            $this->mapper->set_saved($dataset, $crawler->id, $state);

            $response_data = ['status' => 'success'];
        }
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }
    
    public function saved($hash) {

        $crawler = $this->crawler_service->getFromHash($hash);

        if (!$this->crawler_service->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $headers = $this->header_mapper->getFromCrawler($crawler->id);
        
        $datasets = $this->mapper->get_saved($crawler->id);

        $rendered_data = $this->crawler_service->renderTableRows($datasets, $headers, "changedOn");

        return new Payload(Payload::$RESULT_HTML, ['datasets' => $rendered_data, "crawler" => $crawler, "headers" => $headers, "hasCrawlerTable" => true]);
    }

}
