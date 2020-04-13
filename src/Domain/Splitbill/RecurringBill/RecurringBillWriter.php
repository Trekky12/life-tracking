<?php

namespace App\Domain\Splitbill\RecurringBill;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Splitbill\Group\SplitbillGroupService;
use App\Domain\Splitbill\Group\GroupMapper;
use App\Domain\Main\Translator;
use App\Domain\Splitbill\BaseBillWriter;

class RecurringBillWriter extends BaseBillWriter {

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ActivityCreator $activity,
            SplitbillGroupService $group_service,
            GroupMapper $group_mapper,
            Translator $translation,
            RecurringBillMapper $mapper,
            RecurringBillService $service) {
        parent::__construct($logger, $user, $activity, $group_service, $group_mapper, $translation);
        $this->mapper = $mapper;
        $this->service = $service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        $payload = parent::save($id, $data, $additionalData);

        $entry = $payload->getResult();
        if (in_array($payload->getStatus(), [Payload::$STATUS_NEW, Payload::$STATUS_UPDATE])) {
            $this->setLastRun($entry);
        }

        return $payload;
    }

    private function setLastRun(RecurringBill $entry) {
        /**
         * When the entry is new but has an past start date set the last run to this date
         */
        if (is_null($entry->last_run) && !is_null($entry->start)) {
            $start = new \DateTime($entry->start);
            $now = new \DateTime('now');

            $start->setTime(0, 0, 0);
            $now->setTime(0, 0, 0);

            if ($now > $start) {
                $this->mapper->setLastRun($entry->id, $start->format("Y-m-d"));
            }
        }
    }

    public function getParentMapper() {
        return $this->group_mapper;
    }

    public function getObjectViewRoute(): string {
        return 'splitbill_bills_recurring';
    }

    public function getObjectViewRouteParams($entry): array {
        $group = $this->getParentMapper()->get($entry->getParentID());
        return [
            "group" => $group->getHash(),
            "id" => $entry->id
        ];
    }

    public function getModule(): string {
        return "splitbills";
    }

}
