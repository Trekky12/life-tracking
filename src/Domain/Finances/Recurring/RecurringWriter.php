<?php

namespace App\Domain\Finances\Recurring;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class RecurringWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, RecurringMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        $payload = parent::save($id, $data, $user);
        $entry = $payload->getResult();

        $this->setLastRun($entry);

        return $payload;
    }

    private function setLastRun(FinancesEntryRecurring $entry) {
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

    public function getObjectViewRoute(): string {
        return 'finances_categories_edit';
    }

    public function getObjectViewRouteParams(int $id): array {
        return ["id" => $id];
    }

    public function getModule(): string {
        return "finances";
    }

}
