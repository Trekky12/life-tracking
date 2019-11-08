<?php

namespace App\Timesheets\Sheet;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    public function init() {
        $this->model = '\App\Timesheets\Sheet\Sheet';
        $this->index_route = 'timesheets_sheets';
        $this->edit_template = 'timesheets/sheets/edit.twig';

        $this->mapper = new Mapper($this->ci);
        $this->project_mapper = new \App\Timesheets\Project\Mapper($this->ci);
        $this->user_mapper = new \App\User\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('project');
        $project = $this->project_mapper->getFromHash($hash);

        $this->checkAccess($project->id);

        // Date Filter
        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');
        $queryData = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($queryData, $defaultFrom);

        $data = $this->mapper->getTableData($project->id, $from, $to, 0, 'DESC', 20);
        $rendered_data = $this->renderTableRows($project, $data);
        $datacount = $this->mapper->tableCount($project->id, $from, $to);

        $totalSeconds = $this->mapper->tableSum($project->id, $from, $to);

        $users = $this->user_mapper->getAll();

        $range = $this->mapper->getMinMaxDate("start", "end");
        $max = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        return $this->ci->view->render($response, 'timesheets/sheets/index.twig', [
                    "sheets" => $rendered_data,
                    "project" => $project,
                    "datacount" => $datacount,
                    "hasTimesheetTable" => true,
                    "users" => $users,
                    "sum" => $this->ci->get('helper')->splitDateInterval($totalSeconds),
                    "from" => $from,
                    "to" => $to,
                    "min" => $range["min"],
                    "max" => $max,
        ]);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        list($from, $to) = $this->ci->get('helper')->getDateRange($requestData);

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

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => $rendered_data,
                    "sum" => $this->ci->get('helper')->splitDateInterval($totalSeconds)
        ]);
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


        return $this->ci->view->render($response, $this->edit_template, [
                    'entry' => $entry,
                    'project' => $project,
                    'project_users' => $project_users,
                    'users' => $users
        ]);
    }

    // redirect to project overview
    protected function afterSave($id, $data, Request $request) {
        $entry = $this->mapper->get($id);
        $project_id = $entry->project;
        $project = $this->project_mapper->get($project_id);
        $this->index_params = ["project" => $project->hash];

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
    protected function preSave($id, &$data, Request $request) {
        $project_hash = $request->getAttribute("project");
        $entry = $this->project_mapper->getFromHash($project_hash);
        $this->checkAccess($entry->id);
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
        $user = $this->ci->get('helper')->getUser()->id;

        if (!in_array($user, $timesheets_project_users)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

    private function renderTableRows($project, array $sheets) {
        $language = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['datetimeShort']);

        $fmt2 = new \IntlDateFormatter($language, NULL, NULL);
        $fmt2->setPattern($dateFormatPHP['time']);

        $rendered_data = [];
        foreach ($sheets as $sheet) {
            
            list($date, $start, $end) = $sheet->getDateStartEnd($language, $dateFormatPHP['date'], $dateFormatPHP['datetimeShort'], $dateFormatPHP['time']);
            
            $row = [];
            $row[] = $date;
            $row[] = $start;
            $row[] = $end;
            $row[] = $this->ci->get('helper')->splitDateInterval($sheet->diff);

            $row[] = '<a href="' . $this->ci->get('router')->pathFor('timesheets_sheets_edit', ['id' => $sheet->id, 'project' => $project->hash]) . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
            $row[] = '<a href="#" data-url="' . $this->ci->get('router')->pathFor('timesheets_sheets_delete', ['id' => $sheet->id, 'project' => $project->hash]) . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';

            $rendered_data[] = $row;
        }

        return $rendered_data;
    }

}
