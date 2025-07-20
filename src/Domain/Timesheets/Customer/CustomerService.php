<?php

namespace App\Domain\Timesheets\Customer;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\CustomerNotice\CustomerNoticeMapper;
use App\Domain\Timesheets\Project\ProjectService;

class CustomerService extends Service {

    private $project_service;
    private $customer_notice_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        CustomerMapper $mapper,
        ProjectService $project_service,
        CustomerNoticeMapper $customer_notice_mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->customer_notice_mapper = $customer_notice_mapper;
    }

    public function index($hash, $archive = 0) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $customers = $this->mapper->getFromProject($project->id, 'name', $archive);

        // get information about notices
        $customers_ids = array_map(function ($customer) {
            return $customer->id;
        }, $customers);
        $hasNotices = $this->customer_notice_mapper->hasNotices($customers_ids);

        return new Payload(Payload::$RESULT_HTML, [
            'customers' => $customers, 
            "project" => $project, 
            'hasNotices' => $hasNotices,
            "archive" => $archive
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
            "project" => $project
        ]);
    }

    public function getCustomersFromProject($project_id, $archive = null) {
        return $this->mapper->getFromProject($project_id, 'name', $archive);
    }
}
