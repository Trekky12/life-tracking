<?php

namespace App\Domain\Crawler;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\SessionUtility;
use App\Domain\Crawler\CrawlerHeader\CrawlerHeaderMapper;
use App\Domain\Crawler\CrawlerDataset\CrawlerDatasetMapper;
use App\Domain\User\UserService;
use App\Domain\Crawler\CrawlerLink\CrawlerLinkMapper;
use App\Application\Payload\Payload;
use App\Domain\Main\Utility\Utility;

class CrawlerService extends Service {

    private $header_mapper;
    private $dataset_mapper;
    private $user_service;
    private $link_mapper;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CrawlerMapper $mapper, CrawlerHeaderMapper $header_mapper, CrawlerDatasetMapper $dataset_mapper, UserService $user_service, CrawlerLinkMapper $link_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->header_mapper = $header_mapper;
        $this->dataset_mapper = $dataset_mapper;
        $this->user_service = $user_service;
        $this->link_mapper = $link_mapper;
    }

    public function getCrawlersOfUser() {
        return $this->mapper->getUserItems('name');
    }

    public function setFilter($hash, $data) {
        $crawler = $this->getFromHash($hash);

        if (!$this->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = ['status' => 'error'];

        if (array_key_exists("state", $data) && in_array($data["state"], array("createdOn", "changedOn"))) {
            SessionUtility::setSessionVar("crawler_filter_{$crawler->getHash()}", $data["state"]);

            $response_data = ['status' => 'success'];
        }
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    public function view($hash, $from, $to) {

        $crawler = $this->getFromHash($hash);

        if (!$this->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";

        list($datacount, $rendered_data, $headers) = $this->getDatasets($crawler->id, $filter, $hide_diff, $from, $to);

        $links = $this->link_mapper->getFromCrawler($crawler->id, 'position');

        $response_data = [
            "crawler" => $crawler,
            "from" => $from,
            "to" => $to,
            "headers" => $headers,
            "datasets" => $rendered_data,
            "datacount" => $datacount,
            "hasCrawlerTable" => true,
            "filter" => $filter,
            "links" => $this->buildTree($links)
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getDatasets($crawler_id, $filter, $hide_diff, $from, $to) {

        $headers = $this->header_mapper->getFromCrawler($crawler_id, 'position', $hide_diff);
        /**
         * Sorting
         */
        // defaults
        $sortColumn = $filter;
        $sortDirection = "DESC";

        $initialSortColumn = $this->header_mapper->getInitialSortColumn($crawler_id);
        if (!is_null($initialSortColumn)) {
            $sortColumn = $this->getSortFromColumn($initialSortColumn);
            $sortDirection = $initialSortColumn->sort;
        }

        $datacount = $this->dataset_mapper->getCountFromCrawler($crawler_id, $from, $to, $filter);
        $datasets = $this->dataset_mapper->getDataFromCrawler($crawler_id, $from, $to, $filter, $sortColumn, $sortDirection, 20);
        $rendered_data = $this->renderTableRows($datasets, $headers, $filter);

        return [$datacount, $rendered_data, $headers];
    }

    public function table($hash, $from, $to, $requestData): Payload {

        $crawler = $this->getFromHash($hash);

        if (!$this->isMember($crawler->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }


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

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $rendered_data
        ];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

    private function getSortColumnFromColumnIndex($headers, $sortColumnIndex, $sortColumn) {
        $headers_numeric = array_values($headers);

        // first two columns are static, so the correct index is $sortColumnIndex - 2
        // get sort column of array
        if (!empty($sortColumnIndex) && $sortColumnIndex !== "null" && is_numeric($sortColumnIndex) && array_key_exists(($sortColumnIndex - 2), $headers_numeric)) {
            $column = $headers_numeric[$sortColumnIndex - 2];
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
            $sortColumn = "JSON_VALUE(diff, '$.{$columnName}')";
        } else {
            $sortColumn = "JSON_VALUE(data, '$.{$columnName}')";
        }

        if (!is_null($column->datatype)) {
            $sortColumn = "CAST({$sortColumn} AS {$column->datatype})";
        }

        return $sortColumn;
    }

    public function renderTableRows(array $table, $headers, $filter) {
        $rendered_data = [];
        foreach ($table as $dataset) {
            $row = [];

            $row[] = '<span class="save_crawler_dataset ' . ($dataset->isSaved() ? 'is_saved' : '') . '" data-id="' . $dataset->id . '"><span class="star-blank">' . Utility::getFontAwesomeIcon('far fa-star') . '</span><span class="star-filled">' . Utility::getFontAwesomeIcon('fas fa-star') . '</span></span>';

            if ($filter === "changedOn") {
                $row[] = $dataset->changedOn;
            } else {
                $row[] = $dataset->createdOn;
            }

            foreach ($headers as $header) {
                $field = [];

                $content = $dataset->getDataValue($header->field_name);
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

    private function getFilter($crawler) {
        $default = $crawler->filter; //"createdOn";
        $hash = $crawler->getHash();
        return SessionUtility::getSessionVar("crawler_filter_{$hash}", $default);
    }

    public function index() {
        $crawlers = $this->mapper->getUserItems('name');
        return new Payload(Payload::$RESULT_HTML, ['crawlers' => $crawlers]);
    }

    public function edit($entry_id) {
        if ($this->isOwner($entry_id) === false) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'users' => $users]);
    }

    public function buildTree(array $elements, $parentId = null) {
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
