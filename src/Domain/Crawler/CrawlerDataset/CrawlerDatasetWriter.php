<?php

namespace App\Domain\Crawler\CrawlerDataset;

use App\Domain\ObjectWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Crawler\CrawlerService;

class CrawlerDatasetWriter extends ObjectWriter {

    private $crawler_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerDatasetMapper $mapper, CrawlerService $crawler_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->crawler_service = $crawler_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $identifier = array_key_exists("identifier", $data) ? filter_var($data["identifier"], FILTER_SANITIZE_STRING) : null;

        $crawler = $this->crawler_service->getFromHash($additionalData["crawler"]);

        if (!$this->crawler_service->isOwner($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $saveDataset = $this->saveDataset($crawler, $identifier, $data);
        if ($saveDataset !== true) {
            return new Payload(Payload::$STATUS_ERROR, $saveDataset);
        }

        return new Payload(Payload::$STATUS_UPDATE);
    }

    public function saveDataset($crawler, $identifier, $data) {
        $dataset_id = null;
        try {

            $data["crawler"] = $crawler->id;
            $data["user"] = $this->current_user->getUser()->id;

            if (!is_null($identifier)) {
                $dataset = $this->mapper->getIDFromIdentifier($crawler->id, $identifier);

                // entry is already present so it needs to be updated 
                if (!is_null($dataset)) {
                    $dataset_id = $dataset->id;

                    $new = $data["data"];
                    $old = $dataset->getData();
                    $diff = $this->dataDiff($old, $new);

                    $data["id"] = $dataset_id;
                    $data["diff"] = $diff;
                }
            }

            /**
             * Encode data as json
             */
            if (array_key_exists("data", $data) && is_array($data["data"])) {
                $data["data"] = json_encode($data["data"]);
            }
            if (array_key_exists("diff", $data) && is_array($data["diff"])) {
                $data["diff"] = json_encode($data["diff"]);
            }

            $entry = $this->createEntry($data);

            if (is_null($dataset_id)) {
                $this->insertEntry($entry);
            } else {
                $this->updateEntry($entry);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Record Crawler Dataset Error", array("error" => $e->getMessage()));
            return $e->getMessage();
        }
    }

    private function dataDiff($old, $new) {
        return array_diff_assoc($old, $new);
        /*
          $diff = [];
          foreach ($old as $key => $value) {
          if (!array_key_exists($key, $new) || $new[$key] != $value) {
          $diff[$key] = $value;
          } else {
          $diff[$key] = NULL;
          }
          }
          return $diff;
         *
         */
    }

}
