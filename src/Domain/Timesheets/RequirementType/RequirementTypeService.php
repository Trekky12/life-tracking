<?php

namespace App\Domain\Timesheets\RequirementType;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;

class RequirementTypeService extends Service {

    private $project_service;
    protected $customer_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        RequirementTypeMapper $mapper,
        ProjectService $project_service,
        CustomerService $customer_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->customer_service = $customer_service;
    }

    public function index($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $requirement_types = $this->mapper->getFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, [
            'requirement_types' => $requirement_types,
            "project" => $project
        ]);
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
            "project" => $project,
            "dataTypes" => $this->getDataTypes()
        ]);
    }

    private function getDataTypes() {
        return [
            'checkbox' => 'DATATYPE_CHECKBOX'
        ];
    }

    public function viewCustomer($hash, $customer_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->customer_service->isChildOf($project->id, $customer_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $requirements = $this->mapper->getFromProject($project->id, $customer_id);

        return new Payload(Payload::$RESULT_HTML, [
            'requirements' => $requirements,
            "project" => $project
        ]);
    }
}
