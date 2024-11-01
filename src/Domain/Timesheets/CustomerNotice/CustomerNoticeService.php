<?php

namespace App\Domain\Timesheets\CustomerNotice;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Customer\CustomerService;
use App\Domain\Timesheets\NoticeField\NoticeFieldService;
use App\Application\Payload\Payload;

class CustomerNoticeService extends Service {

    protected $project_service;
    protected $customer_service;
    protected $user_service;
    protected $noticefield_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        CustomerNoticeMapper $mapper,
        ProjectService $project_service,
        CustomerService $customer_service,
        NoticeFieldService $noticefield_service
    ) {
        parent::__construct($logger, $user);

        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->customer_service = $customer_service;
        $this->noticefield_service = $noticefield_service;
    }

    public function edit($hash, $customer_id, $requestData) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->customer_service->isChildOf($project->id, $customer_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $customer = $this->customer_service->getEntry($customer_id);

        $fields = $this->noticefield_service->getNoticeFields($project->id, 'customer');

        $view = array_key_exists("view", $requestData) ? filter_var($requestData["view"], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $response_data = [
            'customer' => $customer,
            'project' => $project,
            'hasTimesheetNotice' => true,
            'fields' => $fields,
            'view' => $view
        ];

        return new Payload(Payload::$RESULT_HTML, $response_data);
    }

    public function getData($hash, $customer_id) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        if (!$this->customer_service->isChildOf($project->id, $customer_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->mapper->getNotice($customer_id);

        $response_data = [
            'entry' => $entry
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }
}
