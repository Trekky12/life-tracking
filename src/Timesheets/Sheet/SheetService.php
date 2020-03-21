<?php

namespace App\Timesheets\Sheet;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\Main\Utility\DateUtility;

class SheetService extends \App\Base\Service {

    protected $dataobject = \App\Timesheets\Sheet\Sheet::class;
    protected $dataobject_parent = \App\Timesheets\Project\Project::class;
    protected $element_view_route = 'timesheets_sheets';
    protected $module = "timesheets";

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getTableDataIndex($project, $from, $to, $count = 20) {

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

    public function table($project, $from, $to, $requestData) {
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

    public function getLastSheetWithStartDateToday($project_id) {
        return $this->mapper->getLastSheetWithStartDateToday($project_id);
    }

    public function createCheckInEntry($project, $data) {
        // always create new entry with current timestamp
        $data["start"] = date('Y-m-d H:i');
        $data["project"] = $project->id;
        $data["user"] = $this->current_user->getUser()->id;

        $entry = $this->createEntry($data);
        $this->insertEntry($entry);
    }

    public function createCheckOutEntry($project, $data) {
        // always create new entry with current timestamp
        $data["end"] = date('Y-m-d H:i');
        $data["project"] = $project->id;
        $data["user"] = $this->current_user->getUser()->id;

        $entry = $this->createEntry($data);
        $this->insertEntry($entry);
    }

    public function updateEntryForCheckOut($entry, $data) {
        $entry->end = date('Y-m-d H:i');

        // parse lat/lng/acc values from post data
        $dataObject = new $this->dataobject($data);

        $entry->end_lat = $dataObject->end_lat;
        $entry->end_lng = $dataObject->end_lng;
        $entry->end_acc = $dataObject->end_acc;

        $this->updateEntry($entry);
    }

    protected function getElementViewRoute($entry) {
        $group = $this->getParentObjectService()->getEntry($entry->getParentID());
        $this->element_view_route_params["project"] = $group->getHash();
        return parent::getElementViewRoute($entry);
    }

}
