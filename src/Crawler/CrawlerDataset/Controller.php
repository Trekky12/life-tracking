<?php

namespace App\Crawler\CrawlerDataset;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Crawler\CrawlerDataset\CrawlerDataset';

        $this->mapper = new Mapper($this->ci);
        $this->crawler_mapper = new \App\Crawler\Mapper($this->ci);
    }

    public function saveAPI(Request $request, Response $response) {
        $logger = $this->ci->get('logger');

        $data = $request->getParsedBody();

        $crawlerhash = array_key_exists("crawler", $data) ? filter_var($data["crawler"], FILTER_SANITIZE_STRING) : null;
        $identifier = array_key_exists("identifier", $data) ? filter_var($data["identifier"], FILTER_SANITIZE_STRING) : null;

        $dataset_id = null;
        try {
            $crawler = $this->crawler_mapper->getCrawlerFromHash($crawlerhash);
            $data["crawler"] = $crawler->id;
            $data["user"] = $this->ci->get('helper')->getUser()->id;

            if (!is_null($identifier)) {
                $dataset_id = $this->mapper->getIDFromIdentifier($crawler->id, $identifier);
                if (!is_null($dataset_id)) {
                    $data["id"] = $dataset_id;
                }
            }

            $this->insertOrUpdate($dataset_id, $data);
        } catch (\Exception $e) {

            $logger->addError("Save API " . $this->model, array("error" => $e->getMessage()));
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJSON(array('status' => 'success'));
    }

    /**
     * Encode data as json
     */
    public function preSave($id, &$data) {
        if (array_key_exists("data", $data) && is_array($data["data"])) {
            $data["data"] = json_encode($data["data"]);
        }
    }

}
