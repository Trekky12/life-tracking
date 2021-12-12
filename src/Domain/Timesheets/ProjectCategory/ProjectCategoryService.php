<?php

namespace App\Domain\Timesheets\ProjectCategory;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;

class ProjectCategoryService extends Service {

    private $project_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ProjectCategoryMapper $mapper, ProjectService $project_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
    }

    public function index($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $categories = $this->mapper->getFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, ['categories' => $categories, "project" => $project]);
    }

    public function edit($hash, $entry_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "project" => $project
        ]);
    }
    
    public function getCategoriesFromProject($project_id){
        return $this->mapper->getFromProject($project_id);
    }

}
