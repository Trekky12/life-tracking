<?php

namespace App\Domain\Timesheets\SheetFile;

use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use Slim\Routing\RouteParser;
use App\Domain\Main\Utility\Utility;

class SheetFileService {

    private $logger;
    private $current_user;
    protected $project_service;
    protected $sheet_service;
    protected $sheet_file_mapper;
    protected $router;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectService $project_service,
        SheetService $sheet_service,
        SheetFileMapper $sheet_file_mapper,
        RouteParser $router
    ) {
        $this->logger = $logger;
        $this->current_user = $user;

        $this->project_service = $project_service;
        $this->sheet_service = $sheet_service;
        $this->sheet_file_mapper = $sheet_file_mapper;
        $this->router = $router;
    }

    public function getFullPath($hash, $sheet_id) {
        return dirname(__DIR__, 4) .
            DIRECTORY_SEPARATOR .
            "files" .
            DIRECTORY_SEPARATOR .
            'sheets';
    }

    public function getFiles($hash, $sheet_id) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->sheet_service->isChildOf($project->id, $sheet_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $files = $this->sheet_file_mapper->getFiles($sheet_id);

        $result = [];
        foreach ($files as $file) {
            $result[] = [
                "name" => $file["name"],
                "data" => $this->load_file($hash, $sheet_id, $file["filename"]),
                "type" => $file["type"],
                "encryptedCEK" => $file["encryptedCEK"],
                "delete" => $this->router->urlFor('timesheets_sheets_file_delete', ['sheet' => $sheet_id, 'project' => $project->getHash()]) . '?id=' . $file["id"]
            ];
        }

        return new Payload(Payload::$RESULT_JSON, $result);
    }

    public function load_file($hash, $sheet_id, $filename) {
        $complete_file_name = $this->getFullPath($hash, $sheet_id) . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($complete_file_name)) {
            return false;
        }

        $fileContents = file_get_contents($complete_file_name);
        if ($fileContents === false) {
            return false;
        }

        return base64_encode($fileContents);
    }
}
