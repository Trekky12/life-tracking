<?php

namespace App\Domain\Timesheets\Sheet;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\User\UserService;

class Controller extends \App\Domain\Base\Controller {

    private $project_service;
    private $sheet_export_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            SheetService $service,
            SheetExportService $sheet_export_service,
            ProjectService $project_service,
            UserService $user_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->service->setParentObjectService($project_service);
        $this->sheet_export_service = $sheet_export_service;
        $this->project_service = $project_service;
        $this->user_service = $user_service;
    }

    public function index(Request $request, Response $response) {

        $hash = $request->getAttribute('project');
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $requestData = $request->getQueryParams();

        // Date Filter
        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');
        list($from, $to) = DateUtility::getDateRange($requestData, $defaultFrom);

        $data = $this->service->getTableDataIndex($project, $from, $to);

        $data["users"] = $this->user_service->getAll();

        return $this->twig->render($response, 'timesheets/sheets/index.twig', $data);
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $hash = $request->getAttribute('project');
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        list($from, $to) = DateUtility::getDateRange($requestData);
        $response_data = $this->service->table($project, $from, $to, $requestData);

        return $response->withJson($response_data);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $hash = $request->getAttribute('project');
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $entry = $this->service->getEntry($entry_id);

        $users = $this->user_service->getAll();
        $project_users = $this->project_service->getUsers($project->id);

        return $this->twig->render($response, 'timesheets/sheets/edit.twig', [
                    'entry' => $entry,
                    'project' => $project,
                    'project_users' => $project_users,
                    'users' => $users
        ]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $project_hash = $request->getAttribute("project");
        $project = $this->project_service->getFromHash($project_hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        $data['project'] = $project->id;

        $new_id = $this->doSave($id, $data, null);

        $this->service->setDiff($new_id);

        $redirect_url = $this->router->urlFor('timesheets_sheets', ["project" => $project_hash]);
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        $project_hash = $request->getAttribute("project");
        $project = $this->project_service->getFromHash($project_hash);

        if (!$this->project_service->isMember($project->id)) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
        } else {
            $response_data = $this->doDelete($id);
        }
        return $response->withJson($response_data);
    }

    public function showfastCheckInCheckOut(Request $request, Response $response) {
        $hash = $request->getAttribute('project');
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        // get a existing entry for today with start but without end
        $entry = $this->service->getLastSheetWithStartDateToday($project->id);

        return $this->twig->render($response, 'timesheets/sheets/fast.twig', [
                    "project" => $project,
                    "entry" => $entry
        ]);
    }

    public function fastCheckIn(Request $request, Response $response) {
        $result = array("status" => "success", "data" => 0);
        try {
            $hash = $request->getAttribute('project');
            $project = $this->project_service->getFromHash($hash);

            if (!$this->project_service->isMember($project->id)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            $data = $request->getParsedBody();

            $this->service->createCheckInEntry($project, $data);

            // get a existing entry for today with start but without end
            $entry = $this->service->getLastSheetWithStartDateToday($project->id);

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
            $project = $this->project_service->getFromHash($hash);

            if (!$this->project_service->isMember($project->id)) {
                throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
            }

            $data = $request->getParsedBody();

            // get a existing entry for today with start but without end
            $entry = $this->service->getLastSheetWithStartDateToday($project->id);
            if (!is_null($entry)) {
                $this->service->updateEntryForCheckOut($entry, $data);
                $this->service->setDiff($entry->id);
            } else {
                // otherwise create new entry               
                $this->service->createCheckOutEntry($project, $data);
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
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            throw new \Exception($this->translation->getTranslatedString('NO_ACCESS'), 404);
        }

        // Date Filter
        $requestData = $request->getQueryParams();

        list($from, $to) = DateUtility::getDateRange($requestData);

        $body = $this->sheet_export_service->export($project, $from, $to);

        return $response->write($body)
                        ->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                        ->withHeader('Content-Disposition', 'attachment; filename="' . date('Y-m-d') . '_Export.xlsx"')
                        ->withHeader('Cache-Control', 'max-age=0');
    }

}
