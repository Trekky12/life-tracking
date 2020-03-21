<?php

namespace App\Crawler;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Main\Utility\SessionUtility;
use App\Crawler\CrawlerHeader\CrawlerHeaderService;
use App\Crawler\CrawlerDataset\CrawlerDatasetService;

class CrawlerService extends \App\Base\Service {

    protected $dataobject = \App\Crawler\Crawler::class;
    protected $element_view_route = 'crawlers_edit';
    protected $module = "crawlers";
    private $header_service;
    private $dataset_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            CrawlerHeaderService $header_service,
            CrawlerDatasetService $dataset_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->header_service = $header_service;
        $this->dataset_service = $dataset_service;
    }

    public function getCrawlersOfUser() {
        return $this->mapper->getUserItems('name');
    }

    public function setFilter($crawler, $data) {
        if (array_key_exists("state", $data) && in_array($data["state"], array("createdOn", "changedOn"))) {
            SessionUtility::setSessionVar("crawler_filter_{$crawler->getHash()}", $data["state"]);

            return true;
        }
        return false;
    }

    public function view(Crawler $crawler, $from, $to) {

        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";

        $headers = $this->header_service->getFromCrawler($crawler->id, $hide_diff);

        /**
         * Sorting
         */
        // defaults
        $sortColumn = $filter;
        $sortDirection = "DESC";

        $initialSortColumn = $this->header_service->getInitialSortColumn($crawler->id);
        if (!is_null($initialSortColumn)) {
            $sortColumn = $this->getSortFromColumn($initialSortColumn);
            $sortDirection = $initialSortColumn->sort;
        }

        $datacount = $this->dataset_service->getCountFromCrawler($crawler->id, $from, $to, $filter);
        $datasets = $this->dataset_service->getDataFromCrawler($crawler->id, $from, $to, $filter, $sortColumn, $sortDirection, 20);
        $rendered_data = $this->renderTableRows($datasets, $headers, $filter);

        return [
            "crawler" => $crawler,
            "from" => $from,
            "to" => $to,
            "headers" => $headers,
            "datasets" => $rendered_data,
            "datacount" => $datacount,
            "hasCrawlerTable" => true,
            "filter" => $filter
        ];
    }

    public function table(Crawler $crawler, $from, $to, $requestData) {
        $filter = $this->getFilter($crawler);
        $hide_diff = $filter == "createdOn";

        $headers = $this->header_service->getFromCrawler($crawler->id, 'position', $hide_diff);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = $this->getSortColumnFromColumnIndex($headers, $sortColumnIndex, $filter);
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->dataset_service->getCountFromCrawler($crawler->id, $from, $to, $filter);
        $recordsFiltered = $this->dataset_service->getCountFromCrawler($crawler->id, $from, $to, $filter, $searchQuery);

        $data = $this->dataset_service->getDataFromCrawler($crawler->id, $from, $to, $filter, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($data, $headers, $filter);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $rendered_data
        ];
        return $response_data;
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

        if (!is_null($column->datatype)) {
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

}
