<?php

namespace App\Domain\Timesheets\CustomerNotice;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Customer\CustomerMapper;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;

class CustomerNoticeWriter extends ObjectActivityWriter {

    private $service;
    private $customer_mapper;
    private $project_service;
    private $project_mapper;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            CustomerService $service,
            CustomerNoticeMapper $mapper,
            CustomerMapper $customer_mapper,
            ProjectMapper $project_mapper,
            ProjectService $project_service) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->customer_mapper = $customer_mapper;
        $this->project_mapper = $project_mapper;
        $this->project_service = $project_service;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->service->isChildOf($project->id, $additionalData["customer"])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['customer'] = $additionalData["customer"];

        $payload = parent::save(null, $data, $additionalData);

        $entry = $payload->getResult();

        return $payload->withEntry(["changedOn" => $entry->changedOn]);
    }

    public function getParentMapper() {
        return $this->customer_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_customers_notice_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        $customer = $this->getParentMapper()->get($entry->getParentID());
        $project = $this->project_mapper->get($customer->getParentID());
        return [
            "project" => $project->getHash(),
            "customer" => $customer->id,
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }

}
