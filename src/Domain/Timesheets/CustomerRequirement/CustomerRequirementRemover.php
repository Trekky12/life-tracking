<?php

namespace App\Domain\Timesheets\CustomerRequirement;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\RequirementType\RequirementTypeService;
use App\Domain\Timesheets\RequirementType\RequirementTypeMapper;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Customer\CustomerMapper;

class CustomerRequirementRemover extends ObjectActivityRemover {

    private $service;
    private $project_service;
    private $project_mapper;
    private $requirementtype_service;
    private $requirementtype_mapper;
    private $translation;
    private $customer_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        CustomerRequirementService $service,
        CustomerRequirementMapper $mapper,
        ProjectService $project_service,
        ProjectMapper $project_mapper,
        RequirementTypeService $requirementtype_service,
        RequirementTypeMapper $requirementtype_mapper,
        Translator $translation,
        CustomerMapper $customer_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->service = $service;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->requirementtype_service = $requirementtype_service;
        $this->requirementtype_mapper = $requirementtype_mapper;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->requirementtype_service->isChildOf($project->id, $additionalData["requirementtype"])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($additionalData["requirementtype"], $id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        return parent::delete($id, $additionalData);
    }

    public function getParentMapper() {
        return $this->requirementtype_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_customers_requirements';
    }

    public function getObjectViewRouteParams($entry): array {
        $requirement_type = $this->getParentMapper()->get($entry->getParentID());
        $project = $this->project_mapper->get($requirement_type->getParentID());
        return [
            "project" => $project->getHash(),
            "requirementtype" => $requirement_type->id,
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }
}
