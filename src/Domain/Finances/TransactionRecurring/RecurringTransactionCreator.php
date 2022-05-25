<?php

namespace App\Domain\Finances\TransactionRecurring;

use Psr\Log\LoggerInterface;
use App\Domain\Finances\Transaction\TransactionWriter;
use App\Application\Payload\Payload;

class RecurringTransactionCreator
{

    private $logger;
    private $mapper;
    private $transaction_writer;

    public function __construct(
        LoggerInterface $logger,
        TransactionRecurringMapper $mapper,
        TransactionWriter $transaction_writer
    ) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->transaction_writer = $transaction_writer;
    }

    public function update()
    {

        $mentries = $this->mapper->getRecurringEntries();

        if ($mentries) {
            $this->logger->debug('Recurring Transaction Entries', $mentries);

            foreach ($mentries as $mentry) {
                $this->createElement($mentry);
            }

            $mentry_ids = array_map(function ($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

    public function createEntry($id)
    {
        $mentry = $this->mapper->get($id);

        $entry_id = $this->createElement($mentry);

        return new Payload(Payload::$STATUS_NEW, $mentry);
    }

    private function createElement($mentry)
    {
        $data = [
            'description' => $mentry->description,
            'value' => $mentry->value,
            'account_from' => $mentry->account_from,
            'account_to' => $mentry->account_to,
            'user' => $mentry->user
        ];

        $payload = $this->transaction_writer->save(null, $data);
        $entry = $payload->getResult();

        return $entry->id;
    }
}
