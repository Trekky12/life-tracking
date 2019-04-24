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

        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";
        
        $headers = $this->header_mapper->getFromCrawler($crawler->id, 'position', $hide_diff);
        

        /**
         * Sorting
         */
        // defaults
        $sortColumn = $filter;
        $sortDirection = "DESC";

        $initialSortColumn = $this->header_mapper->getInitialSortColumn($crawler->id);
        if (!is_null($initialSortColumn)) {
            $sortColumn = $this->getSortFromColumn($initialSortColumn);
            $sortDirection = $initialSortColumn->sort;
        }

        $datasets = $this->dataset_mapper->getFromCrawler($crawler->id, $from, $to, $filter, $sortColumn, $sortDirection, 21);
        $datacount = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter);

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
                    "filter" => $filter,
                    "links" => $links_tree
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        list($from, $to) = $this->ci->get('helper')->getDateRange($requestData);

        $hash = $request->getAttribute('hash');
        $crawler = $this->mapper->getCrawlerFromHash($hash);
        
        $this->checkAccess($crawler->id);

        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";

        $headers = $this->header_mapper->getFromCrawler($crawler->id, 'position', $hide_diff);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? null : $search;

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = $this->getSortColumnFromColumnIndex($headers, $sortColumnIndex, $filter);
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter);
        $recordsFiltered = $recordsFiltered = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter, $searchQuery);

        $data = $this->dataset_mapper->tableData($crawler->id, $from, $to, $filter, $start, $length, $searchQuery, $sortColumn, $sortDirection);

        $rendered_data = [];
        foreach ($data as $dataset) {
            $row = [];

            if ($filter === "changedOn") {
                $row[] = $dataset->changedOn;
            } else {
                $row[] = $dataset->createdOn;
            }

            foreach ($headers as $header) {
                $field = [];
                if (!empty($header->field_link)) {
                    $field[] = '<a href="' . $dataset->getDataValue($header->field_link) . '" target="_blank">';
                }

                if (!empty($header->prefix)) {
                    $field[] = $header->getHTML("prefix");
                }

                if (!empty($header->field_content)) {
                    $field[] = $header->getHTML();
                } elseif (intval($header->diff) === 1) {
                    $field[] = $dataset->getDataValue($header->field_name, "diff");
                } else {
                    $field[] = $dataset->getDataValue($header->field_name);
                }

                if (!empty($header->suffix)) {
                    $field[] = $header->getHTML("suffix");
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

        if (array_key_exists("hash", $data)) {
            $hash = filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS);
            $crawler = $this->mapper->getCrawlerFromHash($hash);
            
            $this->checkAccess($crawler->id);

            if (array_key_exists("state", $data) && in_array($data["state"], array("createdOn", "changedOn"))) {
                $this->ci->get('helper')->setSessionVar("crawler_filter_{$hash}", $data["state"]);
                return $response->withJSON(array('status' => 'success'));
            }
        }
        return $response->withJSON(array('status' => 'error'));
    }

    private function getFilter($crawler) {
        $default = $crawler->filter; //"createdOn";
        $hash = $crawler->hash;
        return $this->ci->get('helper')->getSessionVar("crawler_filter_{$hash}", $default);
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

    private function getSortColumnFromColumnIndex($headers, $sortColumnIndex, $sortColumn) {
        $headers_numeric = array_values($headers);

        // get sort column of array
        if (!empty($sortColumnIndex) && $sortColumnIndex !== "null" && is_numeric($sortColumnIndex) && count($headers) >= $sortColumnIndex) {
            $column = $headers_numeric[$sortColumnIndex - 1];
            // is this column really sortable?
            if (intval($column->sortable) === 1) {
                $sortColumn = $this->getSortFromColumn($column);
            }
        }
        return $sortColumn;
    }

    private function getSortFromColumn($column) {
        $columnName = $column->field_name;

        // JSON_EXTRACT
        if (intval($column->diff) === 1) {
            $sortColumn = "JSON_EXTRACT(diff, '$.{$columnName}')";
        } else {
            $sortColumn = "JSON_EXTRACT(data, '$.{$columnName}')";
        }
        return $sortColumn;
    }

}
