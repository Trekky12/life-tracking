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
        $crawlers = $this->mapper->getUserItems('name');
        return $this->ci->view->render($response, 'crawlers/index.twig', ['crawlers' => $crawlers]);
    }

    public function view(Request $request, Response $response) {

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data);

        $hash = $request->getAttribute('crawler');
        $crawler = $this->mapper->getFromHash($hash);

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

        $datacount = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter);
        $datasets = $this->dataset_mapper->getDataFromCrawler($crawler->id, $from, $to, $filter, $sortColumn, $sortDirection, 20);
        $rendered_data = $this->renderTableRows($datasets, $headers, $filter);        

        $links = $this->link_mapper->getFromCrawler($crawler->id, 'position');
        $links_tree = $this->buildTree($links);

        return $this->ci->view->render($response, 'crawlers/view.twig', [
                    "crawler" => $crawler,
                    "from" => $from,
                    "to" => $to,
                    "headers" => $headers,
                    "datasets" => $rendered_data,
                    "datacount" => $datacount,
                    "hasCrawlerTable" => true,
                    "filter" => $filter,
                    "links" => $links_tree
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        list($from, $to) = $this->ci->get('helper')->getDateRange($requestData);

        $hash = $request->getAttribute('crawler');
        $crawler = $this->mapper->getFromHash($hash);

        $this->checkAccess($crawler->id);

        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";

        $headers = $this->header_mapper->getFromCrawler($crawler->id, 'position', $hide_diff);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = $this->getSortColumnFromColumnIndex($headers, $sortColumnIndex, $filter);
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter);
        $recordsFiltered = $this->dataset_mapper->getCountFromCrawler($crawler->id, $from, $to, $filter, $searchQuery);

        $data = $this->dataset_mapper->getDataFromCrawler($crawler->id, $from, $to, $filter, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($data, $headers, $filter);

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $rendered_data
        ]);
    }

    /**
     * Does the user have access to this dataset?
     */
    protected function preSave($id, &$data, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preEdit($id, Request $request) {
        $this->allowOwnerOnly($id);
    }

    protected function preDelete($id, Request $request) {
        $this->allowOwnerOnly($id);
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

    protected function afterSave($id, $data, Request $request) {
        $dataset = $this->mapper->get($id);
        if (empty($dataset->hash)) {
            $hashids = new Hashids('', 10);
            $hash = $hashids->encode($id);
            $this->mapper->setHash($id, $hash);
        }
    }

    public function setFilter(Request $request, Response $response) {

        $data = $request->getParsedBody();        
        $hash = $request->getAttribute('crawler');

        if (!is_null($hash)) {
            $crawler = $this->mapper->getFromHash($hash);

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

        if(!is_null($column->datatype)){
            $sortColumn = "CAST({$sortColumn} AS {$column->datatype})";
        }

        return $sortColumn;
    }

    private function renderTableRows(array $table, $headers, $filter) {
        $rendered_data = [];
        foreach ($table as $dataset) {
            $row = [];

            if ($filter === "changedOn") {
                $row[] = $dataset->changedOn;
            } else {
                $row[] = $dataset->createdOn;
            }

            foreach ($headers as $header) {
                $field = [];
                
                $content = $dataset->getDataValue($header->field_name);;
                if (!empty($header->field_content)) {
                    $content = $header->getHTML();
                } elseif (intval($header->diff) === 1) {
                    $content = $dataset->getDataValue($header->field_name, "diff");
                } 
                
                if (!empty($header->field_link)) {

                    $link = $dataset->getDataValue($header->field_link);
                    if (intval($header->diff) === 1) {
                        $link = $dataset->getDataValue($header->field_link, "diff");
                    }

                    $field[] = '<a href="' . $link . '" target="_blank">';
                }

                if (!empty($header->prefix) && !empty($content)) {
                    $field[] = $header->getHTML("prefix");
                }

                $field[] = $content;

                if (!empty($header->suffix) && !empty($content)) {
                    $field[] = $header->getHTML("suffix");
                }

                if (!empty($header->field_link)) {
                    $field[] = '</a>';
                }
                $row[] = implode("", $field);
            }
            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

}
