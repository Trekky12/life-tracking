<?php

namespace App\Domain\Timesheets\CustomerRequirement;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;
use App\Domain\Timesheets\RequirementType\RequirementTypeService;
use Slim\Routing\RouteParser;

class CustomerRequirementService extends Service {

    private $project_service;
    protected $customer_service;
    protected $requirementtype_service;
    protected $router;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        CustomerRequirementMapper $mapper,
        ProjectService $project_service,
        CustomerService $customer_service,
        RequirementTypeService $requirementtype_service,
        RouteParser $router
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->customer_service = $customer_service;
        $this->requirementtype_service = $requirementtype_service;
        $this->router = $router;
    }

    public function index($hash, $requirementtype_id, $valid = 0) {
        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->requirementtype_service->isChildOf($project->id, $requirementtype_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $requirement_type = $this->requirementtype_service->getEntry($requirementtype_id);

        $requirements = $this->mapper->getFromType($requirementtype_id, $valid);

        $customers = $this->customer_service->getCustomersFromProject($project->id, false);

        $customers_states = $this->getCustomersState($project, $requirementtype_id);

        return new Payload(Payload::$RESULT_HTML, [
            "requirements" => $requirements,
            "requirement_type" => $requirement_type,
            "project" => $project,
            "customers" => $customers,
            "customers_states" => $customers_states,
            "valid" => $valid
        ]);
    }

    public function edit($hash, $requirementtype_id, $customer = null, $view = null) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->requirementtype_service->isChildOf($project->id, $requirementtype_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $requirement_type = $this->requirementtype_service->getEntry($requirementtype_id);

        $customers = $this->customer_service->getCustomersFromProject($project->id, false);

        return new Payload(Payload::$RESULT_HTML, [
            "requirement_type" => $requirement_type,
            "project" => $project,
            "customers" => $customers,
            "selected_customer" => $customer,
            "view" => $view
        ]);
    }

    public function getCustomersState($project, $requirement_type = null, $view = null) {
        $states =  $this->getMapper()->getCustomersState($project->id, $requirement_type);

        $results = [];
        foreach ($states as $state) {

            $state["url"] = $this->router->urlFor('timesheets_customers_requirements_edit', ['project' => $project->getHash(), "requirementtype" => $state["requirement_type_id"]]) . "?customer=" . $state["customer_id"] . (!is_null($view) ? "&view=" . $view : "");

            if (is_null($requirement_type)) {
                $customer = $state["customer_id"];
                if (!array_key_exists($customer, $results)) {
                    $results[$customer] = [];
                }
                $results[$customer][] = $state;
            } else {
                $results[] = $state;
            }
        }

        return $results;
    }
}
