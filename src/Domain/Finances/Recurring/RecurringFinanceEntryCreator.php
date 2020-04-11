<?php

namespace App\Domain\Finances\Recurring;

use Psr\Log\LoggerInterface;
use App\Domain\Finances\FinancesEntry;

class RecurringFinanceEntryCreator {

    private $logger;
    private $mapper;
    private $finances_entry_writer;

    public function __construct(LoggerInterface $logger, RecurringMapper $mapper, RecurringFinanceEntryWriter $finances_entry_writer) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->finances_entry_writer = $finances_entry_writer;
    }

    public function update() {

        $mentries = $this->mapper->getRecurringEntries();

        if ($mentries) {
            $this->logger->addDebug('Recurring Entries', $mentries);

            foreach ($mentries as $mentry) {
                $entry = new FinancesEntry([
                    'type' => $mentry->type,
                    'category' => $mentry->category,
                    'description' => $mentry->description,
                    'value' => $mentry->value,
                    'common' => $mentry->common,
                    'common_value' => $mentry->common_value,
                    'notice' => $mentry->notice,
                    'user' => $mentry->user,
                    'fixed' => 1,
                    'paymethod' => $mentry->paymethod
                ]);
                $this->finances_entry_writer->addFinanceEntry($entry);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

}
