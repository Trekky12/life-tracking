<?php

namespace App\Domain\Finances\Recurring;

use App\Domain\ObjectWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Base\CurrentUser;

class RecurringFinanceEntryWriter extends ObjectWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, FinancesMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function addFinanceEntry($entry) {
        return $this->insertEntry($entry);
    }

}
