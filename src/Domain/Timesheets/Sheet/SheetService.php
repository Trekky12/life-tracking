<?php

namespace App\Domain\Timesheets\Sheet;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Main\Utility\DateUtility;
use App\Application\Payload\Payload;

class SheetService extends Service {

    protected $project_service;
    protected $user_service;
    protected $settings;
    protected $router;

    public function __construct(LoggerInterface $logger, CurrentUser $user, SheetMapper $mapper, ProjectService $project_service, UserService $user_service, Settings $settings, RouteParser $router) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->user_service = $user_service;
        $this->settings = $settings;
        $this->router = $router;
    }

    public function view($hash, $from, $to): Payload {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $response_data = $this->getTableDataIndex($project, $from, $to);

        $response_data["users"] = $this->user_service->getAll();

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    private function getTableDataIndex($project, $from, $to, $count = 20) {

        $data = $this->mapper->getTableData($project->id, $from, $to, 0, 'DESC', $count);
        $rendered_data = $this->renderTableRows($project, $data);
        $datacount = $this->mapper->tableCount($project->id, $from, $to);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $range = $this->mapper->getMinMaxDate("start", "end");
        $max = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        return [
            "sheets" => $rendered_data,
            "project" => $project,
            "datacount" => $datacount,
            "hasTimesheetTable" => true,
            "sum" => DateUtility::splitDateInterval($totalSeconds),
            "from" => $from,
            "to" => $to,
            "min" => $range["min"],
            "max" => $max,
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

        $recordsTotal = $this->mapper->tableCount($project->id, $from, $to);
        $recordsFiltered = $this->mapper->tableCount($project->id, $from, $to, $searchQuery);

        $data = $this->mapper->getTableData($project->id, $from, $to, $sortColumnIndex, $sortDirection, $length, $start, $searchQuery);
        $rendered_data = $this->renderTableRows($project, $data);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to, $searchQuery);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $rendered_data,
            "sum" => DateUtility::splitDateInterval($totalSeconds)
        ];

        return $response_data;
    }

    private function renderTableRows($project, array $sheets) {
        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $rendered_data = [];
        foreach ($sheets as $sheet) {

            list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetime'], $dateFormatPHP['time']);

            $row = [];
            $row[] = $date;
            $row[] = $start;
            $row[] = $end;
            $row[] = DateUtility::splitDateInterval($sheet->diff);

            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_edit', ['id' => $sheet->id, 'project' => $project->getHash()]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[] = '<a href="#" data-url="' . $this->router->urlFor('timesheets_sheets_delete', ['id' => $sheet->id, 'project' => $project->getHash()]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }

    public function setDiff($id) {
        $entry = $this->mapper->get($id);
        // get and save diff
        $diff = $entry->getDiff();
        if (!is_null($diff)) {
            $this->mapper->set_diff($id, $diff);
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

        $response_data = [
            'entry' => $entry,
            'project' => $project,
            'project_users' => $project_users,
            'users' => $users
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

        return new Payload(Payload::$RESULT_HTML, ["project" => $project, "entry" => $entry]);
    }

    public function getLastSheetWithStartDateToday($project_id) {
        return $this->mapper->getLastSheetWithStartDateToday($project_id);
    }

}
