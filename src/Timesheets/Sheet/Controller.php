<?php

namespace App\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Timesheets\Sheet\Sheet';
    protected $parent_model = '\App\Timesheets\Project\Project';
    protected $index_route = 'timesheets_sheets';
    protected $edit_template = 'timesheets/sheets/edit.twig';
    protected $element_view_route = 'timesheets_sheets';
    protected $module = "timesheets";
    private $project_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, CurrentUser $current_user) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);


        $this->mapper = new Mapper($this->db, $this->translation, $current_user);
        $this->project_mapper = new \App\Timesheets\Project\Mapper($this->db, $this->translation, $current_user);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $this->checkAccess($project->id);

        // Date Filter
        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');
        $queryData = $request->getQueryParams();
        list($from, $to) = $this->helper->getDateRange($queryData, $defaultFrom);

        $data = $this->mapper->getTableData($project->id, $from, $to, 0, 'DESC', 20);
        $rendered_data = $this->renderTableRows($project, $data);
        $datacount = $this->mapper->tableCount($project->id, $from, $to);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $users = $this->user_mapper->getAll();

        $range = $this->mapper->getMinMaxDate("start", "end");
        $max = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        return $this->twig->render($response, 'timesheets/sheets/index.twig', [
                    "sheets" => $rendered_data,
                    "project" => $project,
                    "datacount" => $datacount,
                    "hasTimesheetTable" => true,
                    "users" => $users,
                    "sum" => $this->helper->splitDateInterval($totalSeconds),
                    "from" => $from,
                    "to" => $to,
                    "min" => $range["min"],
                    "max" => $max,
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        list($from, $to) = $this->helper->getDateRange($requestData);

        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $this->checkAccess($project->id);

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
            "sum" => $this->helper->splitDateInterval($totalSeconds)
        ];
        return $response->withJson($response_data);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }
        $this->preEdit($entry_id, $request);

        $users = $this->user_mapper->getAll();
        $project_users = $this->project_mapper->getUsers($project->id);


        return $this->twig->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'project' => $project,
                    'project_users' => $project_users,
                    'users' => $users
        ]);
    }

    // redirect to project overview
    protected function afterSave($id, array $data, Request $request) {
        $entry = $this->mapper->get($id);
        $project_id = $entry->project;
        $project = $this->project_mapper->get($project_id);
        $this->index_params = ["project" => $project->getHash()];

        // get and save diff
        $diff = $entry->getDiff();
        if (!is_null($diff)) {
            $this->mapper->set_diff($id, $diff);
        }
    }

    /**
     * Is the user allowed to save/edit/delete this 
     * (is he a member of the project?)
     */
    protected function preSave($id, array &$data, Request $request) {
        $project_hash = $request->getAttribute("project");
        $entry = $this->project_mapper->getFromHash($project_hash);
        $this->checkAccess($entry->id);

        $data['project'] = $entry->id;
    }

    protected function preEdit($id, Request $request) {
        $project_hash = $request->getAttribute("project");
        $entry = $this->project_mapper->getFromHash($project_hash);
        $this->checkAccess($entry->id);
    }

    protected function preDelete($id, Request $request) {
        $project_hash = $request->getAttribute("project");
        $entry = $this->project_mapper->getFromHash($project_hash);
        $this->checkAccess($entry->id);
    }

    /**
     * Is the user a member of this project?
     */
    private function checkAccess($id) {
        $timesheets_project_users = $this->project_mapper->getUsers($id);
        $user = $this->current_user->getUser()->id;

        if (!in_array($user, $timesheets_project_users)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }
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
            $row[] = $this->helper->splitDateInterval($sheet->diff);

            $row[] = '<a href="' . $this->router->urlFor('timesheets_sheets_edit', ['id' => $sheet->id, 'project' => $project->getHash()]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[] = '<a href="#" data-url="' . $this->router->urlFor('timesheets_sheets_delete', ['id' => $sheet->id, 'project' => $project->getHash()]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>';

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }

    public function showfastCheckInCheckOut(Request $request, Response $response) {
        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $this->checkAccess($project->id);

        // get a existing entry for today with start but without end
        $entry = $this->mapper->getLastSheetWithStartDateToday($project->id);

        return $this->twig->render($response, 'timesheets/sheets/fast.twig', [
                    "project" => $project,
                    "entry" => $entry
        ]);
    }

    public function fastCheckIn(Request $request, Response $response) {
        $result = array("status" => "success", "data" => 0);
        try {
            $hash = $request->getAttribute('project');
            $project = $this->project_mapper->getFromHash($hash);

            $this->checkAccess($project->id);

            $data = $request->getParsedBody();

            // always create new entry with current timestamp
            $data["start"] = date('Y-m-d H:i');
            $data["project"] = $project->id;
            $data["user"] = $this->current_user->getUser()->id;

            $this->insertOrUpdate(null, $data, $request);

            // get a existing entry for today with start but without end
            $entry = $this->mapper->getLastSheetWithStartDateToday($project->id);
            $result["data"] = !is_null($entry) ? 1 : 0;
        } catch (\Exception $e) {
            $result["status"] = "error";
            $result["message"] = $e->getMessage();
        }

        $this->flash->clearMessages();

        return $response->withJSON($result);
    }

    public function fastCheckOut(Request $request, Response $response) {

        $result = array("status" => "success", "data" => 0);
        try {
            $hash = $request->getAttribute('project');
            $project = $this->project_mapper->getFromHash($hash);

            $this->checkAccess($project->id);

            $data = $request->getParsedBody();

            // get a existing entry for today with start but without end
            $entry = $this->mapper->getLastSheetWithStartDateToday($project->id);
            if (!is_null($entry)) {
                $entry->end = date('Y-m-d H:i');

                // parse values from post data
                $dataModell = new $this->model($data);
                $entry->end_lat = $dataModell->end_lat;
                $entry->end_lng = $dataModell->end_lng;
                $entry->end_acc = $dataModell->end_acc;

                $this->insertOrUpdate($entry->id, $entry->get_fields(), $request);
            } else {
                // otherwise create new entry
                $data["end"] = date('Y-m-d H:i');
                $data["project"] = $project->id;
                $data["user"] = $this->current_user->getUser()->id;

                $this->insertOrUpdate(null, $data, $request);
            }

            $result["data"] = !is_null($entry) ? 1 : 0;
        } catch (\Exception $e) {
            $result["status"] = "error";
            $result["message"] = $e->getMessage();
        }

        $this->flash->clearMessages();

        return $response->withJSON($result);
    }

    /**
     * 
     */
    public function export(Request $request, Response $response) {
        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $this->checkAccess($project->id);

        // Date Filter
        $queryData = $request->getQueryParams();
        list($from, $to) = $this->helper->getDateRange($queryData);

        // get Data
        $data = $this->mapper->getTableData($project->id, $from, $to, 0, 'ASC', null);
        $rendered_data = $this->renderTableRows($project, $data);
        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];
        $fmtDate = new \IntlDateFormatter($language, NULL, NULL);
        $fmtDate->setPattern($dateFormatPHP["date"]);

        $fromDate = new \DateTime($from);
        $toDate = new \DateTime($to);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));

        // Project Name
        $sheet->setCellValue('A1', $project->name);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->mergeCells("A1:F1");

        // Range
        $sheet->setCellValue('A2', $fmtDate->format($fromDate) . " " . $this->translation->getTranslatedString("TO") . " " . $fmtDate->format($toDate));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells("A2:F2");

        // Table Header
        $sheet->setCellValue('A4', $this->translation->getTranslatedString("DATE"));
        $sheet->setCellValue('B4', $this->translation->getTranslatedString("TIMESHEETS_COME"));
        $sheet->setCellValue('C4', $this->translation->getTranslatedString("TIMESHEETS_LEAVE"));
        $sheet->setCellValue('D4', $this->translation->getTranslatedString("DIFFERENCE"));
        $sheet->getStyle('A4:D4')->applyFromArray(
                ['borders' => [
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]
        );
        $sheet->getStyle('A4:D4')->getFont()->setBold(true);


        $excelTime = "[$-F400]h:mm:ss AM/PM";
        $excelDate = $dateFormatPHP['date'];
        $excelDateTime = $dateFormatPHP['datetime'];
        $excelTimeDiff = "[hh]:mm:ss";

        // Table Data
        $offset = 4;
        $idx = 0;
        foreach ($data as $timesheet) {

            //list($date, $start, $end) = $timesheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetimeShort'], $dateFormatPHP['time']);
            //$diff = $this->helper->splitDateInterval($timesheet->diff);
            //$sheet->setCellValue('A' . ($idx + 1 + $offset), $date);
            //$sheet->setCellValue('B' . ($idx + 1 + $offset), $start);
            //$sheet->setCellValue('C' . ($idx + 1 + $offset), $end);
            //$sheet->setCellValue('D' . ($idx + 1 + $offset), $diff);

            $start = $timesheet->getStartDateTime();
            $end = $timesheet->getEndDateTime();

            $row = ($idx + 1 + $offset);

            // only show time on end date when start date and end date are on the same day
            if (!is_null($timesheet->start) && !is_null($timesheet->end) && $timesheet->getStartDateTime()->format('Y-m-d') == $timesheet->getEndDateTime()->format('Y-m-d')) {
                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelTime);
            } elseif (!is_null($timesheet->end)) {
                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelDateTime);
            }

            if (!is_null($timesheet->start)) {
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start));

                $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($start));
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode($excelTime);
            } elseif (!is_null($timesheet->end)) {
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));

                $sheet->setCellValue('C' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($end));
                $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode($excelTime);
            }

            if (!is_null($timesheet->start) && !is_null($timesheet->end)) {
                $sheet->setCellValue('D' . $row, "=C" . $row . "-B" . $row);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode($excelTimeDiff);
            }

            $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode($excelDate);

            $idx++;
        }

        // Table Footer
        $firstRow = (1 + $offset);
        $sumRow = ($idx + 1 + $offset);
        $sheet->setCellValue('D' . $sumRow, "=SUM(D" . $firstRow . ":D" . ($sumRow - 1) . ")");
        $sheet->getStyle('D' . $sumRow)->getNumberFormat()->setFormatCode($excelTimeDiff);

        $sheet->getStyle('A' . $sumRow . ':D' . $sumRow)->applyFromArray(
                ['borders' => [
                        'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE],
                        'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                    ],
                ]
        );

        // auto width
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);

        // sheet protection
        $sheet->getProtection()->setSheet(true);
        $sheet->getStyle("A" . $firstRow . ":C" . ($sumRow - 1))
                ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
        );
        $sheet->getStyle("A1:F2")
                ->getProtection()->setLocked(
                \PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED
        );

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        /**
         * PSR-7 not supported
         * @see https://github.com/PHPOffice/PhpSpreadsheet/issues/28
         * so a temporary file in a specific directory is generated and later deleted 
         */
        $path = __DIR__ . '/../../../files/';
        $excelFileName = tempnam($path, 'phpxltmp');
        $writer->save($excelFileName);


        /**
         * We should use a Stream Object but then deleting is not possible, so instead use file_get_contents
         * @see https://gist.github.com/odan/a7a1eb3c876c9c5b2ffd2db55f29fdb8
         * @see https://odan.github.io/2017/12/16/creating-and-downloading-excel-files-with-slim.html
         * @see https://stackoverflow.com/a/51675156
         */
        /*
         * $stream = fopen($excelFileName, 'r+');
         * $response->withBody(new \Slim\Http\Stream($stream))->...
         */
        $body = file_get_contents($excelFileName);
        unlink($excelFileName);

        return $response->write($body)
                        ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . date('Y-m-d') . '_Export.xlsx"')
                        ->withHeader('Cache-Control', 'max-age=0');
    }

    protected function getElementViewRoute($entry) {
        $group = $this->getParentObjectMapper()->get($entry->getParentID());
        $this->element_view_route_params["project"] = $group->getHash();
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectMapper() {
        return $this->project_mapper;
    }

}
