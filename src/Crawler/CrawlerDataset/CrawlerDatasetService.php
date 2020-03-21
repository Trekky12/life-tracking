<?php

namespace App\Crawler\CrawlerDataset;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class CrawlerDatasetService extends \App\Base\Service {

    protected $dataobject = \App\Crawler\CrawlerDataset\CrawlerDataset::class;
    protected $dataobject_parent = \App\Crawler\Crawler::class;
    protected $element_view_route = 'crawlers_view';
    protected $create_activity = false;
    protected $module = "crawlers";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getCountFromCrawler($crawler_id, $from, $to, $filter, $searchQuery = "%") {
        return $this->mapper->getCountFromCrawler($crawler_id, $from, $to, $filter, $searchQuery);
    }

    public function getDataFromCrawler($crawler_id, $from, $to, $filter, $sortColumn, $sortDirection, $length = 20, $start = 0, $searchQuery = "%") {
        return $this->mapper->getDataFromCrawler($crawler_id, $from, $to, $filter, $sortColumn, $sortDirection, $length, $start, $searchQuery);
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
            return false;
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

    protected function getElementViewRoute($entry) {
        $crawler = $this->getParentObjectService()->getEntry($entry->getParentID());
        $this->element_view_route_params["crawler"] = $crawler->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectService() {
        return $this->crawler_service;
    }

}
