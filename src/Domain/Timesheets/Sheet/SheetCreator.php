<?php

namespace App\Domain\Timesheets\Sheet;

use Psr\Log\LoggerInterface;
use App\Domain\Timesheets\Project\ProjectService;
use App\Application\Payload\Payload;

class SheetCreator {

    private $logger;
    private $mapper;
    private $sheet_writer;
    private $project_service;

    public function __construct(
        LoggerInterface $logger,
        SheetMapper $mapper,
        SheetWriter $sheet_writer,
        ProjectService $project_service
    ) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->sheet_writer = $sheet_writer;
        $this->project_service = $project_service;
    }

    public function createEntry($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data = [
            "start" => date('Y-m-d H:i:s')
        ];

        $this->logger->debug('Create new sheet', array("project" => $project, "data" => $data));

        $entry = $this->sheet_writer->save(null, $data, ["project" => $project->getHash()]);

        return $entry;
    }
}
