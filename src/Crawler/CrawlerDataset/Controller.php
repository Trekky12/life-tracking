<?php

namespace App\Crawler\CrawlerDataset;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Crawler\CrawlerDataset\CrawlerDataset';
    protected $parent_model = '\App\Crawler\Crawler';
    protected $element_view_route = 'crawlers_view';
    protected $create_activity = false;
    protected $module = "crawlers";
    private $crawler_mapper;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function saveAPI(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $crawler_hash = $request->getAttribute('crawler');
        $identifier = array_key_exists("identifier", $data) ? filter_var($data["identifier"], FILTER_SANITIZE_STRING) : null;

        $dataset_id = null;
        try {
            $crawler = $this->crawler_mapper->getFromHash($crawler_hash);

            $this->allowCrawlerOwnerOnly($crawler);

            $data["crawler"] = $crawler->id;
            $data["user"] = $this->ci->get('helper')->getUser()->id;

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

            $this->insertOrUpdate($dataset_id, $data, $request);
        } catch (\Exception $e) {
            $this->logger->addError("Save API " . $this->model, array("error" => $e->getMessage()));
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJSON(array('status' => 'success'));
    }

    /**
     * Encode data as json
     */
    protected function preSave($id, array &$data, Request $request) {
        if (array_key_exists("data", $data) && is_array($data["data"])) {
            $data["data"] = json_encode($data["data"]);
        }
        if (array_key_exists("diff", $data) && is_array($data["diff"])) {
            $data["diff"] = json_encode($data["diff"]);
        }
    }

    private function allowCrawlerOwnerOnly($crawler) {
        $user = $this->ci->get('helper')->getUser()->id;
        if ($crawler->user !== $user) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
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
        $crawler = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["crawler"] = $crawler->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->crawler_mapper;
    }

}
