<?php

namespace App\Domain\Finances\Recurring;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;

class RecurringFinanceEntryWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, FinancesMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function addFinanceEntry($entry) {
        return $this->insertEntry($entry);
    }

    public function getObjectViewRoute(): string {
        return 'finances_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "finances";
    }

}
