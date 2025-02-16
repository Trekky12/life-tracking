<?php

namespace App\Domain\Timesheets\SheetNotice;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Sheet\SheetMapper;
use App\Domain\Timesheets\Project\ProjectMapper;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\Main\Translator;
use App\Domain\Timesheets\Customer\CustomerMapper;

class SheetNoticeWriter extends ObjectActivityWriter {

    private $service;
    private $sheet_mapper;
    private $project_service;
    private $project_mapper;
    private $translation;
    private $customer_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        SheetService $service,
        SheetNoticeMapper $mapper,
        SheetMapper $sheet_mapper,
        ProjectMapper $project_mapper,
        ProjectService $project_service,
        Translator $translation,
        CustomerMapper $customer_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->service = $service;
        $this->mapper = $mapper;
        $this->sheet_mapper = $sheet_mapper;
        $this->project_mapper = $project_mapper;
        $this->project_service = $project_service;
        $this->translation = $translation;
        $this->customer_mapper = $customer_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload {

        $project = $this->project_service->getFromHash($additionalData["project"]);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->service->isChildOf($project->id, $additionalData["sheet"])) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $data['sheet'] = $additionalData["sheet"];

        // get last notice as id for update
        //$notice_id = $this->mapper->hasNotice($additionalData["sheet"]);
        //$data["id"] = $notice_id;

        $payload = parent::save(null, $data, $additionalData);

        $entry = $payload->getResult();

        return $payload->withEntry(["changedOn" => $entry->changedOn]);
    }

    public function getParentMapper() {
        return $this->sheet_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'timesheets_sheets_notice_view';
    }

    public function getObjectViewRouteParams($entry): array {
        $sheet = $this->getParentMapper()->get($entry->getParentID());
        $project = $this->project_mapper->get($sheet->getParentID());
        return [
            "project" => $project->getHash(),
            "sheet" => $sheet->id,
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "timesheets";
    }

    protected function getAdditionalInformation($entry): ?string {
        $sheet = $this->getParentMapper()->get($entry->getParentID());
        if ($sheet->customer) {
            $project = $this->project_mapper->get($sheet->getParentID());
            $customerDescription = $project->customers_name_singular ? $project->customers_name_singular : $this->translation->getTranslatedString("TIMESHEETS_CUSTOMER");

            $customer = $this->customer_mapper->get($sheet->customer);

            return sprintf("%s: %s", $customerDescription, $customer->name);
        }
        return parent::getAdditionalInformation($entry);
    }
}
