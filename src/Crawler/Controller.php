<?php

namespace App\Crawler;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Hashids\Hashids;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Crawler\Crawler';
        $this->index_route = 'crawlers';
        $this->edit_template = 'crawlers/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
        $this->dataset_mapper = new \App\Crawler\CrawlerDataset\Mapper($this->ci);
        $this->header_mapper = new \App\Crawler\CrawlerHeader\Mapper($this->ci);
        $this->link_mapper = new \App\Crawler\CrawlerLink\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $crawlers = $this->mapper->getVisibleCrawlers('name');
        return $this->ci->view->render($response, 'crawlers/index.twig', ['crawlers' => $crawlers]);
    }

    public function view(Request $request, Response $response) {

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        $hash = $request->getAttribute('hash');
        $crawler = $this->mapper->getCrawlerFromHash($hash);

        $this->checkAccess($crawler->id);

        $headers = $this->header_mapper->getFromCrawler($crawler->id, 'position');

        $datasets = $this->dataset_mapper->getFromCrawler($crawler->id, $from, $to, $this->getFilter(), $this->getFilter(), "DESC", 20);
        $datacount = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $this->getFilter());

        $links = $this->link_mapper->getFromCrawler($crawler->id, 'position');
        $links_tree = $this->buildTree($links);

        return $this->ci->view->render($response, 'crawlers/view.twig', [
                    "crawler" => $crawler,
                    "from" => $from,
                    "to" => $to,
                    "headers" => $headers,
                    "datasets" => $datasets,
                    "datacount" => $datacount,
                    "hasCrawlerTable" => true,
                    "filter" => $this->getFilter(),
                    "links" => $links_tree
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        list($from, $to) = $this->ci->get('helper')->getDateRange($requestData);

        $hash = $request->getAttribute('hash');
        $crawler = $this->mapper->getCrawlerFromHash($hash);

        $this->checkAccess($crawler->id);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? null : $search;

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $this->getFilter());
        $recordsFiltered = $recordsFiltered = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $this->getFilter(), $searchQuery);

        $data = $this->dataset_mapper->tableData($crawler->id, $from, $to, $this->getFilter(), $start, $length, $searchQuery, $sortColumn, $sortDirection);

        // sort not possible
        $headers = $this->header_mapper->getFromCrawler($crawler->id, 'position');

        $rendered_data = [];
        foreach ($data as $dataset) {
            $row = [];

            if ($this->getFilter() === "changedOn") {
                $row[] = $dataset->changedOn;
            } else {
                $row[] = $dataset->createdOn;
            }

            foreach ($headers as $header) {
                $field = [];
                if (!empty($header->field_link)) {
                    $field[] = '<a href="' . $dataset->getDataValue($header->field_link) . '" target="_blank">';
                }

                if (!empty($header->field_content)) {
                    $field[] = $header->getFieldContent();
                } else {
                    $field[] = $dataset->getDataValue($header->field_name);
                }

                if (!empty($header->field_link)) {
                    $field[] = '</a>';
                }
                $row[] = implode("", $field);
            }
            $rendered_data[] = $row;
        }


        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $rendered_data
                        ]
        );
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id) {
        $this->allowOwnerOnly($id);
    }

    private function allowOwnerOnly($crawler_id) {
        $user = $this->ci->get('helper')->getUser()->id;
        if (!is_null($crawler_id)) {
            $crawler = $this->mapper->get($crawler_id);

            if ($crawler->user !== $user) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
            }
        }
    }

    /**
     * Is the user allowed to view this crawler?
     */
    private function checkAccess($id) {
        $crawler_users = $this->mapper->getUsers($id);
        $user = $this->ci->get('helper')->getUser()->id;
        if (!in_array($user, $crawler_users)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    protected function afterSave($id, $data) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

    public function setFilter(Request $request, Response $response) {
        $data = $request->getParsedBody();

        if (array_key_exists("state", $data) && in_array($data["state"], array("createdOn", "changedOn"))) {
            $this->ci->get('helper')->setSessionVar('crawler_filter', $data["state"]);
        }

        return $response->withJSON(array('status' => 'success'));
    }

    private function getFilter() {
        return $this->ci->get('helper')->getSessionVar('crawler_filter', "createdOn");
    }

    private function buildTree(array $elements, $parentId = null) {
        $branch = array();

        foreach ($elements as $element) {
            if ($element->parent == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

}
