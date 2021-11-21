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

class SheetService extends Service {

    protected $project_service;
    protected $project_category_service;
    protected $user_service;
    protected $settings;
    protected $router;
    protected $translation;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SheetMapper $mapper,
            ProjectService $project_service,
            ProjectCategoryService $project_category_service,
            UserService $user_service,
            Settings $settings,
            RouteParser $router,
            Translator $translation) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_category_service = $project_category_service;
        $this->user_service = $user_service;
        $this->settings = $settings;
        $this->router = $router;
        $this->translation = $translation;
    }

    public function view($hash, $from, $to, $categories): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);

        $selected_categories = $categories;
        /* if (empty($categories)) {
          $selected_categories = array_map(function ($cat) {
          return $cat->id;
          }, $project_categories);
          } */

        $response_data = $this->getTableDataIndex($project, $from, $to, $selected_categories);

        $response_data["users"] = $this->user_service->getAll();
        $response_data["categories"] = $project_categories;

        $response_data["categories_selected"] = $selected_categories;

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTableDataIndex($project, $from, $to, $selected_categories = [], $count = 20) {

        $range = $this->mapper->getMinMaxDate("start", "end", $project->id, "project");
        $minTotal = $range["min"];
        $maxTotal = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        // Month Filter
        $d1 = new \DateTime('first day of this month');
        $minMonth = $d1->format('Y-m-d');
        $d2 = new \DateTime('last day of this month');
        $maxMonth = $d2->format('Y-m-d');

        if ($project->default_view == "month") {
            $from = !is_null($from) ? $from : $minMonth;
            $to = !is_null($to) ? $to : $maxMonth;
        } elseif ($project->default_view == "all") {
            $from = !is_null($from) ? $from : $minTotal;
            $to = !is_null($to) ? $to : $maxTotal;
        }

        $data = $this->mapper->getTableData($project->id, $from, $to, $selected_categories, 0, 'DESC', $count);
        $rendered_data = $this->renderTableRows($project, $data);
        $datacount = $this->mapper->tableCount($project->id, $from, $to, $selected_categories);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to, $selected_categories);

        $sum = DateUtility::splitDateInterval($totalSeconds);
        if ($project->has_duration_modifications > 0 && $totalSeconds > 0) {
            $totalSecondsModified = $this->mapper->tableSum($project->id, $from, $to, $selected_categories, "%", "t.duration_modified");
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
                "month" => $minMonth
            ],
            "max" => [
                "total" => $maxTotal,
                "month" => $maxMonth
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

        $recordsTotal = $this->mapper->tableCount($project->id, $from, $to, $categories);
        $recordsFiltered = $this->mapper->tableCount($project->id, $from, $to, $categories, $searchQuery);

        $data = $this->mapper->getTableData($project->id, $from, $to, $categories, $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($project, $data);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to, $categories, $searchQuery);

        $sum = DateUtility::splitDateInterval($totalSeconds);
        if ($project->has_duration_modifications > 0 && $totalSeconds > 0) {
            $totalSecondsModified = $this->mapper->tableSum($project->id, $from, $to, $categories, $searchQuery, "t.duration_modified");
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

    private function renderTableRows($project, array $sheets) {
        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

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
            $row[] = $sheet->categories;

            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_notice_edit', ['sheet' => $sheet->id, 'project' => $project->getHash()]) . '">' . $this->translation->getTranslatedString("NOTICE") . '</a>';
            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_edit', ['id' => $sheet->id, 'project' => $project->getHash()]) . '">' . Utility::getFontAwesomeIcon('fas fa-edit') . '</a>';
            $row[] = '<a href="#" data-url="' . $this->router->urlFor('timesheets_sheets_delete', ['id' => $sheet->id, 'project' => $project->getHash()]) . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</a>';

            $rendered_data[] = $row;
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

    public function edit($hash, $entry_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $users = $this->user_service->getAll();
        $project_users = $this->project_service->getUsers($project->id);

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $sheet_categories = !is_null($entry) ? $this->mapper->getCategoriesFromSheet($entry->id) : [];

        $response_data = [
            'entry' => $entry,
            'project' => $project,
            'project_users' => $project_users,
            'users' => $users,
            'categories' => $project_categories,
            'sheet_categories' => $sheet_categories
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

        return new Payload(Payload::$RESULT_HTML, [
            "project" => $project,
            "categories" => $project_categories,
            "entry" => $entry
        ]);
    }

    public function getLastSheetWithStartDateToday($project_id) {
        return $this->mapper->getLastSheetWithStartDateToday($project_id);
    }

    public function showExport($hash, $from, $to) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, [
            "project" => $project,
            "categories" => $project_categories,
            "from" => $from,
            "to" => $to
        ]);
    }

    public function setCategories($hash, $data) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $recipe = array_key_exists("recipe", $data) && !empty($data["recipe"]) ? intval(filter_var($data["recipe"], FILTER_SANITIZE_NUMBER_INT)) : null;
        $date = array_key_exists("date", $data) && !empty($data["date"]) ? filter_var($data["date"], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $type = array_key_exists("type", $data) && !empty($data["type"]) ? filter_var($data["type"], FILTER_SANITIZE_STRING) : null;
        if (!in_array($type, ["assign", "remove"])) {
            return new Payload(Payload::$STATUS_ERROR, "WRONG_TYPE");
        }

        $sheets = [];
        if (array_key_exists("sheets", $data) && is_array($data["sheets"])) {
            $sheets = filter_var_array($data["sheets"], FILTER_SANITIZE_NUMBER_INT);
        }

        $categories = [];
        if (array_key_exists("categories", $data) && is_array($data["categories"])) {
            $categories = filter_var_array($data["categories"], FILTER_SANITIZE_NUMBER_INT);
        }

        $result = false;
        if (count($sheets) > 0 && count($categories) > 0) {
            if ($type == "assign") {
                $result = $this->mapper->addCategoriesToSheets($sheets, $categories);
            } elseif ($type == "remove") {
                $result = $this->mapper->removeCategoriesFromSheets($sheets, $categories);
            }
        }

        if (!$result) {
            return new Payload(Payload::$STATUS_NEW);
        }
        return new Payload(Payload::$STATUS_ERROR);
    }

}
