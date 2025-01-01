<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryService;
use App\Domain\Main\Utility\DateUtility;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\Project;
use App\Domain\Main\Utility\Utility;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\SheetNotice\SheetNoticeMapper;
use App\Domain\Timesheets\Customer\CustomerService;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Domain\Timesheets\ProjectCategoryBudget\ProjectCategoryBudgetService;

class SheetService extends Service {

    protected $project_service;
    protected $project_category_service;
    protected $user_service;
    protected $settings;
    protected $router;
    protected $translation;
    protected $sheet_notice_mapper;
    protected $customer_service;
    protected $noticefield_service;
    protected $project_category_budget_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SheetMapper $mapper,
        ProjectService $project_service,
        ProjectCategoryService $project_category_service,
        UserService $user_service,
        Settings $settings,
        RouteParser $router,
        Translator $translation,
        SheetNoticeMapper $sheet_notice_mapper,
        CustomerService $customer_service,
        NoticeFieldService $noticefield_service,
        ProjectCategoryBudgetService $project_category_budget_service
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_category_service = $project_category_service;
        $this->user_service = $user_service;
        $this->settings = $settings;
        $this->router = $router;
        $this->translation = $translation;
        $this->sheet_notice_mapper = $sheet_notice_mapper;
        $this->customer_service = $customer_service;
        $this->noticefield_service = $noticefield_service;
        $this->project_category_budget_service = $project_category_budget_service;
    }

    public function view($hash, $from, $to, $categories, $billed = null, $payed = null, $planned = null, $customer = null): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id, 0);

        $selected_categories = $categories;
        /* if (empty($categories)) {
          $selected_categories = array_map(function ($cat) {
          return $cat->id;
          }, $project_categories);
          } */
        $include_empty_categories = true;

        $response_data = $this->getTableDataIndex($project, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer);

        $response_data["users"] = $this->user_service->getAll();
        $response_data["categories"] = $project_categories;

        $response_data["categories_selected"] = $selected_categories;

        $response_data["categories_selected_query"] = ["categories" => $selected_categories];

        $response_data["payed"] = $payed;
        $response_data["billed"] = $billed;
        $response_data["planned"] = $planned;

        $response_data["customers"] = $customers;
        $response_data["customer"] = $customer;

        $response_data["has_category_budgets"] = $this->project_category_budget_service->hasCategoryBudgets($project->id);

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTableDataIndex($project, $from, $to, $selected_categories = [], $include_empty_categories = true, $billed = null, $payed = null, $planned = null, $customer = null, $count = 20) {

        $range = $this->getMapper()->getMinMaxDate("start", "end", $project->id, "project");
        $minTotal = $range["min"];
        $maxTotal = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        // Month Filter
        $d1 = new \DateTime('first day of this month');
        $minMonth = $d1->format('Y-m-d');
        $d2 = new \DateTime('last day of this month');
        $maxMonth = $d2->format('Y-m-d');

        $q1Start = (new \DateTime('first day of january'))->format('Y-m-d');
        $q1End = (new \DateTime('last day of march'))->format('Y-m-d');

        $q2Start = (new \DateTime('first day of april'))->format('Y-m-d');
        $q2End = (new \DateTime('last day of june'))->format('Y-m-d');

        $q3Start = (new \DateTime('first day of july'))->format('Y-m-d');
        $q3End = (new \DateTime('last day of september'))->format('Y-m-d');

        $q4Start = (new \DateTime('first day of october'))->format('Y-m-d');
        $q4End = (new \DateTime('last day of december'))->format('Y-m-d');

        if ($project->default_view == "month") {
            $from = !is_null($from) ? $from : $minMonth;
            $to = !is_null($to) ? $to : $maxMonth;
        } elseif ($project->default_view == "all") {
            $from = !is_null($from) ? $from : $minTotal;
            $to = !is_null($to) ? $to : $maxTotal;
        }

        $include_empty_categories = true;

        $data = $this->getMapper()->getTableData($project->id, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer, 1, 'DESC', $count);
        $rendered_data = $this->renderTableRows($project, $data, false);
        $datacount = $this->getMapper()->tableCount($project->id, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer);

        $totalSeconds = $this->getMapper()->tableSum($project->id, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer);
        $totalSecondsModified = $this->getMapper()->tableSum($project->id, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer, "%", "t.duration_modified");

        $sum = DateUtility::splitDateInterval($totalSeconds);
        if ($project->has_duration_modifications > 0 && ($totalSeconds > 0 || $totalSecondsModified > 0)) {
            $sum = DateUtility::splitDateInterval($totalSecondsModified) . ' (' . $sum . ')';
        }

        return [
            "sheets" => $rendered_data,
            "project" => $project,
            "datacount" => $datacount,
            "hasTimesheetTable" => true,
            "sum" => $sum,
            "from" => $from,
            "to" => $to,
            "min" => [
                "total" => $minTotal,
                "month" => $minMonth,
                "q1" => $q1Start,
                "q2" => $q2Start,
                "q3" => $q3Start,
                "q4" => $q4Start,
            ],
            "max" => [
                "total" => $maxTotal,
                "month" => $maxMonth,
                "q1" => $q1End,
                "q2" => $q2End,
                "q3" => $q3End,
                "q4" => $q4End,
            ],
        ];
    }

    public function table($hash, $from, $to, $requestData): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $table = $this->getTableData($project, $from, $to, $requestData);

        return new Payload(Payload::$RESULT_JSON, $table);
    }

    private function getTableData($project, $from, $to, $requestData) {
        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sortColumnIndex = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $categoriesList = array_key_exists("categories", $requestData) ? filter_var($requestData["categories"], FILTER_SANITIZE_STRING) : null;
        $categories = [];
        if (!empty($categoriesList)) {
            $categories = explode(",", $categoriesList);
        }

        $include_empty_categories = true;

        $billed = array_key_exists('billed', $requestData) && $requestData['billed'] !== '' ? intval(filter_var($requestData['billed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $payed = array_key_exists('payed', $requestData) && $requestData['payed'] !== '' ? intval(filter_var($requestData['payed'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $planned = array_key_exists('planned', $requestData) && $requestData['planned'] !== '' ? intval(filter_var($requestData['planned'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $customer = array_key_exists('customer', $requestData) && $requestData['customer'] !== '' ? intval(filter_var($requestData['customer'], FILTER_SANITIZE_NUMBER_INT)) : null;

        $recordsTotal = $this->mapper->tableCount($project->id, $from, $to, $categories, $include_empty_categories, $billed, $payed, $planned, $customer);
        $recordsFiltered = $this->mapper->tableCount($project->id, $from, $to, $categories, $include_empty_categories, $billed, $payed, $planned, $customer, $searchQuery);

        $data = $this->mapper->getTableData($project->id, $from, $to, $categories, $include_empty_categories, $billed, $payed, $planned, $customer, $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($project, $data, true);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to, $categories, $include_empty_categories, $billed, $payed, $planned, $customer, $searchQuery);
        $totalSecondsModified = $this->mapper->tableSum($project->id, $from, $to, $categories, $include_empty_categories, $billed, $payed, $planned, $customer, $searchQuery, "t.duration_modified");

        $sum = DateUtility::splitDateInterval($totalSeconds);
        if ($project->has_duration_modifications > 0 && ($totalSeconds > 0 || $totalSecondsModified > 0)) {
            $sum = DateUtility::splitDateInterval($totalSecondsModified) . ' (' . $sum . ')';
        }

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $rendered_data,
            "sum" => $sum
        ];

        return $response_data;
    }

    private function renderTableRows($project, array $sheets, $filter = false) {
        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        // get information about notices
        $sheet_ids = array_map(function ($sheet) {
            return $sheet->id;
        }, $sheets);
        $hasNotices = $this->sheet_notice_mapper->hasNotices($sheet_ids);

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id);

        $rendered_data = [];
        foreach ($sheets as $sheet) {

            list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);

            $time = DateUtility::splitDateInterval($sheet->duration);
            if ($project->has_duration_modifications > 0 && $sheet->duration_modified > 0 && $sheet->duration !== $sheet->duration_modified) {
                $time = DateUtility::splitDateInterval($sheet->duration_modified) . ' (' . $time . ')';
            }

            $row = [];
            $row[] = '<input type="checkbox" name="check_row" data-id="' . $sheet->id . '">';
            $row[] = $date;
            $row[] = $start;
            $row[] = $end;
            $row[] = $time;
            if (!$filter || $customers) {
                $row[] = $sheet->customerName;
            }
            if (!$filter || $project_categories) {
                $row[] = $sheet->categories;
            }

            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_notice_edit', ['sheet' => $sheet->id, 'project' => $project->getHash()]) . '">' . (in_array($sheet->id, $hasNotices) ? $this->translation->getTranslatedString("TIMESHEETS_NOTICE_EDIT") : $this->translation->getTranslatedString("TIMESHEETS_NOTICE_ADD")) . '</a>';
            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_edit', ['id' => $sheet->id, 'project' => $project->getHash()]) . '">' . Utility::getFontAwesomeIcon('fas fa-pen-to-square') . '</a>';
            $row[] = '<a href="#" data-url="' . $this->router->urlFor('timesheets_sheets_delete', ['id' => $sheet->id, 'project' => $project->getHash()]) . '" data-warning="' . (in_array($sheet->id, $hasNotices) ? $this->translation->getTranslatedString("TIMESHEETS_SHEET_DELETE_WARNING_NOTICE") : "") . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</a>';

            $rendered_data[] = ["data" => $row, "attributes" => ["data-billed" => $sheet->is_billed, "data-payed" => $sheet->is_payed, "data-planned" => $sheet->is_planned]];
        }

        return $rendered_data;
    }

    public function setDuration(Sheet $entry, Project $project, $duration_modification = 0) {

        // get and save duration
        $duration = $entry->calculateDuration();
        if (!is_null($duration)) {
            $this->mapper->set_duration($entry->id, $duration);

            switch ($duration_modification) {
                case 0:
                    $this->mapper->set_duration_modified($entry->id, $duration);
                    break;
                case 1:
                    $conversion_rate = (intval($project->has_duration_modifications) > 0 && $project->time_conversion_rate > 0) ? $project->time_conversion_rate : 1;
                    $this->mapper->set_duration_modified($entry->id, $duration * $conversion_rate);
                    break;
                case 2:
                    // do nothing since duration_modified is already set from the input box
            }
        }
    }

    public function edit($hash, $entry_id, $requestData) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();
        $project_users = $this->project_service->getUsers($project->id);

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $sheet_categories = !is_null($entry) ? $this->mapper->getCategoriesFromSheet($entry->id) : [];
        $customers = $this->customer_service->getCustomersFromProject($project->id);

        $start_date = new \DateTime();
        $start = $start_date->format('Y-m-d H:i');
        $startParam = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        if ($startParam != null) {
            $startParamDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $startParam);
            if (!$startParamDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startParam)) {
                $startParamDate = \DateTime::createFromFormat('Y-m-d', $startParam);
            }
            $start = $startParamDate->format('Y-m-d H:i');
        }

        $end = null;
        $endParam = array_key_exists("end", $requestData) ? filter_var($requestData["end"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        if ($endParam != null) {
            $endParamDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $endParam);
            if (!$endParamDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $startParam)) {
                $endParamDate = \DateTime::createFromFormat('Y-m-d', $endParamDate);
            }
            $end = $endParamDate->format('Y-m-d H:i');
        }

        $series = [];
        if ($entry) {
            $start = $entry->start;
            $end = $entry->end;
            $default_duration = $project->default_duration;
            if (is_null($entry) && !is_null($default_duration)) {
                $end_date = new \DateTime('+' . $default_duration . ' seconds');
                $end = $end_date->format('Y-m-d H:i');
            }

            $series = $this->getMapper()->getSeriesSheets($project->id, $entry->id);
            $series_ids = array_keys(
                array_map(function ($sheet) {
                    return $sheet->id;
                }, $series)
            );

            $language = $this->settings->getAppSettings()['i18n']['php'];
            $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

            $series = array_map(function ($sheet) use ($language, $dateFormatPHP) {
                list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);
                return sprintf("%s: %s - %s", $date, $start, $end);
            }, $series);

            $remaining_sheets = $series;
            $previous_sheets = [];

            $index = array_search($entry->id, $series_ids);
            if ($index !== false) {
                $remaining_sheets = array_slice($series, $index + 1);
                $previous_sheets = array_slice($series, 0, $index);
            }
        }

        $view = array_key_exists("view", $requestData) ? filter_var($requestData["view"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $response_data = [
            'entry' => $entry,
            'project' => $project,
            'project_users' => $project_users,
            'users' => $users,
            'categories' => $project_categories,
            'sheet_categories' => $sheet_categories,
            'customers' => $customers,
            'start' => $start,
            'end' => $end,
            'units' => Project::getUnits(),
            'series' => $series,
            'previous_sheets' => $previous_sheets,
            'remaining_sheets' => $remaining_sheets,
            'view' => $view
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function showfastCheckInCheckOut($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        // get a existing entry for today with start but without end
        $entry = $this->getLastSheetWithStartDateToday($project->id);

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id, 0);

        return new Payload(Payload::$RESULT_HTML, [
            "project" => $project,
            "categories" => $project_categories,
            "customers" => $customers,
            "entry" => $entry,
            "has_category_budgets" => $this->project_category_budget_service->hasCategoryBudgets($project->id)
        ]);
    }

    public function getLastSheetWithStartDateToday($project_id) {
        return $this->mapper->getLastSheetWithStartDateToday($project_id);
    }

    public function showExport($hash, $from, $to, $categories, $billed = null, $payed = null, $planned = null, $customer = null): Payload {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id, 0);

        $customer_fields = $this->noticefield_service->getNoticeFields($project->id, 'customer');

        return new Payload(Payload::$RESULT_HTML, [
            "project" => $project,
            "categories" => $project_categories,
            "from" => $from,
            "to" => $to,
            "categories_selected" => $categories,
            "billed" => $billed,
            "payed" => $payed,
            "planned" => $planned,
            "customers" => $customers,
            "customer" => $customer,
            "customer_fields" => $customer_fields,
            "has_category_budgets" => $this->project_category_budget_service->hasCategoryBudgets($project->id)
        ]);
    }

    public function setCategories($hash, $data) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $type = array_key_exists("type", $data) && !empty($data["type"]) ? filter_var($data["type"], FILTER_SANITIZE_STRING) : null;
        if (!in_array($type, ["assign", "remove"])) {
            return new Payload(Payload::$STATUS_ERROR, "WRONG_TYPE");
        }

        $sheets = [];
        if (array_key_exists("sheets", $data) && is_array($data["sheets"])) {
            $sheets = filter_var_array($data["sheets"], FILTER_SANITIZE_NUMBER_INT);
        }
        $project_sheets = $this->mapper->getSheetIDsFromProject($project->id);
        $sheets = array_filter($sheets, function ($sheet) use ($project_sheets) {
            return in_array($sheet, $project_sheets);
        });

        $categories = [];
        if (array_key_exists("categories", $data) && is_array($data["categories"])) {
            $categories = filter_var_array($data["categories"], FILTER_SANITIZE_NUMBER_INT);
        }
        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $categories = array_filter($categories, function ($cat) use ($project_categories) {
            return in_array($cat, array_keys($project_categories));
        });

        $result = false;
        if (count($sheets) > 0 && count($categories) > 0) {
            if ($type == "assign") {
                $result = $this->mapper->addCategoriesToSheets($sheets, $categories);
            } elseif ($type == "remove") {
                $result = $this->mapper->removeCategoriesFromSheets($sheets, $categories);
            }
        }

        if ($result) {
            return new Payload(Payload::$STATUS_NEW);
        }
        return new Payload(Payload::$STATUS_ERROR);
    }

    public function setOptions($hash, $data) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $option = array_key_exists("option", $data) && !empty($data["option"]) ? filter_var($data["option"], FILTER_SANITIZE_STRING) : null;
        if (!in_array($option, ["billed", "not_billed", "payed", "not_payed", "planned", "happened"])) {
            return new Payload(Payload::$STATUS_ERROR, "WRONG_TYPE");
        }

        $sheets = [];
        if (array_key_exists("sheets", $data) && is_array($data["sheets"])) {
            $sheets = filter_var_array($data["sheets"], FILTER_SANITIZE_NUMBER_INT);
        }
        $project_sheets = $this->mapper->getSheetIDsFromProject($project->id);
        $sheets = array_filter($sheets, function ($sheet) use ($project_sheets) {
            return in_array($sheet, $project_sheets);
        });

        $result = false;
        if (count($sheets) > 0) {
            if ($option == "billed") {
                $result = $this->mapper->setSheetsBilledState($sheets, 1);
            } elseif ($option == "not_billed") {
                $result = $this->mapper->setSheetsBilledState($sheets, 0);
            } elseif ($option == "payed") {
                $result = $this->mapper->setSheetsPayedState($sheets, 1);
            } elseif ($option == "not_payed") {
                $result = $this->mapper->setSheetsPayedState($sheets, 0);
            } elseif ($option == "planned") {
                $result = $this->mapper->setSheetsPlannedState($sheets, 1);
            } elseif ($option == "happened") {
                $result = $this->mapper->setSheetsPlannedState($sheets, 0);
            }
        }

        if ($result) {
            return new Payload(Payload::$STATUS_NEW);
        }
        return new Payload(Payload::$STATUS_ERROR);
    }

    public function calendar($hash, $from, $to, $categories, $billed = null, $payed = null, $planned = null,  $customer = null): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id);

        $project_notice_fields = $this->noticefield_service->getNoticeFields($project->id, 'project');
        $project_notice_fields_names = array_map(function ($noticefield) {
            return $noticefield->name;
        }, $project_notice_fields);
        $has_legend = in_array("legend", $project_notice_fields_names);

        $selected_categories = $categories;
        $include_empty_categories = true;

        $response_data = $this->getTableDataIndex($project, $from, $to, $selected_categories, $include_empty_categories, $billed, $payed, $planned, $customer);

        $response_data["users"] = $this->user_service->getAll();
        $response_data["categories"] = $project_categories;

        $response_data["categories_selected"] = $selected_categories;

        $response_data["categories_selected_query"] = ["categories" => $selected_categories];

        $response_data["payed"] = $payed;
        $response_data["billed"] = $billed;
        $response_data["planned"] = $planned;

        $response_data["customers"] = $customers;
        $response_data["customer"] = $customer;

        $response_data["hasTimesheetCalendar"] = true;

        $response_data["slot_min_time"] = $project->slot_min_time;
        $response_data["slot_max_time"] = $project->slot_max_time;

        $response_data["has_category_budgets"] = $this->project_category_budget_service->hasCategoryBudgets($project->id);

        $response_data["has_legend"] = $has_legend;
        $response_data["hasTimesheetNotice"] = $has_legend;

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function events($hash, $requestData): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $end = array_key_exists("end", $requestData) ? filter_var($requestData["end"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $startDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $start);
        $endDate = \DateTime::createFromFormat(\DateTimeInterface::ATOM, $end);

        $from = $startDate->format('Y-m-d');
        $to = $endDate->format('Y-m-d');

        $sheets = $this->getMapper()->getTableData($project->id, $from, $to, null);

        // get information about notices
        $sheet_ids = array_map(function ($sheet) {
            return $sheet->id;
        }, $sheets);
        $hasNotices = $this->sheet_notice_mapper->hasNotices($sheet_ids);

        $customers = $this->customer_service->getCustomersFromProject($project->id, 0);

        $events = [];

        foreach ($sheets as $timesheet) {

            $st = new \DateTime($timesheet->start);
            $e = new \DateTime($timesheet->end);

            $title = '';
            if ($timesheet->customerName) {
                $title .= $timesheet->customerName;
            } elseif ($timesheet->categories) {
                $title .= $timesheet->categories;
            }

            list($date, $start, $end) = $timesheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);

            $series = $this->getMapper()->getSeriesSheets($project->id, $timesheet->id);
            $series_ids = array_keys(
                array_map(function ($sheet) {
                    return $sheet->id;
                }, $series)
            );

            $series = array_map(function ($sheet) use ($language, $dateFormatPHP) {
                list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);
                return sprintf("%s: %s - %s", $date, $start, $end);
            }, $series);

            $remaining_sheets = $series;
            $previous_sheets = [];

            $index = array_search($timesheet->id, $series_ids);
            if ($index !== false) {
                $remaining_sheets = array_slice($series, $index + 1);
                $previous_sheets = array_slice($series, 0, $index);
            }

            $event = [
                'title' => $title,
                'start' => $st->format('Y-m-d H:i:s'),
                'end' => $e->format('Y-m-d H:i:s'),
                'extendedProps' => [
                    'edit' => $this->router->urlFor('timesheets_sheets_edit', ['id' => $timesheet->id, 'project' => $project->getHash()]) . "?view=calendar",
                    'delete' => $this->router->urlFor('timesheets_sheets_delete', ['id' => $timesheet->id, 'project' => $project->getHash()]),
                    'start' => $st->format('Y-m-d H:i:s'),
                    'end' => $e->format('Y-m-d H:i:s'),
                    'date' => sprintf("%s %s - %s", $date, $start, $end),
                    'customer' => $timesheet->customerName ? $timesheet->customerName : '',
                    'categories' => $timesheet->categories ? $timesheet->categories : '',
                    'is_planned' => $timesheet->is_planned,
                    'is_billed' => $timesheet->is_billed,
                    'is_payed' => $timesheet->is_payed,
                    'reference_sheet' => $timesheet->reference_sheet,
                    'series' => $series_ids,
                    'previous' => $previous_sheets,
                    'remaining' => $remaining_sheets,
                    'sheet_notice' => $timesheet->id ? $this->router->urlFor('timesheets_sheets_notice_view', ['sheet' => $timesheet->id, 'project' => $project->getHash()]) . "?view=calendar" : null,
                    'has_sheet_notice' => in_array($timesheet->id, $hasNotices),
                    'customer_notice' => $timesheet->customer ? $this->router->urlFor('timesheets_customers_notice_view', ['customer' => $timesheet->customer, 'project' => $project->getHash()]) . "?view=calendar" : null
                ]
            ];

            if (array_key_exists($timesheet->customer, $customers)) {
                $customer = $customers[$timesheet->customer];
                if (!is_null($customer->background_color)) {
                    $event['backgroundColor'] = $customer->background_color;
                    $event['borderColor'] = $customer->background_color;
                }
                if (!is_null($customer->text_color)) {
                    $event['textColor'] = $customer->text_color;
                }
            }

            $events[] = $event;
        }

        return new Payload(Payload::$RESULT_JSON, $events);
    }
}
