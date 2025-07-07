<?php

namespace App\Domain\Timesheets\CustomerRequirement;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\RequirementType\RequirementTypeService;
use App\Domain\Timesheets\RequirementType\RequirementTypeMapper;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Customer\CustomerMapper;

class CustomerRequirementWriter extends ObjectActivityWriter {

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
        $this->service = $service;
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_mapper = $project_mapper;
        $this->requirementtype_service = $requirementtype_service;
        $this->requirementtype_mapper = $requirementtype_mapper;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

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

        $data['requirement_type'] = $additionalData["requirementtype"];

        $requirement_type = $this->requirementtype_service->getEntry($additionalData["requirementtype"]);

        $currentDate = new \DateTime('today');
        switch ($requirement_type->validity_period) {
            case 'month':
                $year = (int) $currentDate->format('Y');
                $month = $currentDate->format('M');
                $data['start'] = (new \DateTime("first day of $month $year"))->format('Y-m-d');
                $data['end'] = (new \DateTime("last day of $month $year"))->format('Y-m-d');
                break;
            case 'quarter':
                $year = (int) $currentDate->format('Y');
                $month = (int) $currentDate->format('n');
                $quarter = (int) ceil($month / 3);
                $quarters = SheetService::getQuarterMonths();
                $data['start'] = (new \DateTime("first day of {$quarters[$quarter]['start']} $year"))->format('Y-m-d');
                $data['end'] = (new \DateTime("last day of {$quarters[$quarter]['end']} $year"))->format('Y-m-d');
                break;
            case 'year':
                $year = (int) $currentDate->format('Y');
                $data['start'] = (new \DateTime("first day of $year"))->format('Y-m-d');
                $data['end'] = (new \DateTime("last day of $year"))->format('Y-m-d');
                break;
        }

        return parent::save(null, $data, $additionalData);
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

    protected function getAdditionalInformation($entry): ?string {
        if ($entry->customer) {
            $requirement_type = $this->getParentMapper()->get($entry->getParentID());
            $project = $this->project_mapper->get($requirement_type->getParentID());
            $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");

            $customer = $this->customer_mapper->get($entry->customer);

            return sprintf("%s: %s", $customerDescription, $customer->name);
        }
        return parent::getAdditionalInformation($entry);
    }
}
