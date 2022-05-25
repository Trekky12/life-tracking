<?php

namespace App\Domain\Finances\Account;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Transaction\TransactionWriter;

class AccountWriter extends ObjectActivityWriter
{

    private $transaction_writer;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        AccountMapper $mapper,
        TransactionWriter $transaction_writer
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->transaction_writer = $transaction_writer;
    }

    public function save($id, $data, $additionalData = null): Payload
    {

        if (!is_null($id)) {
            $oldEntry = $this->mapper->get($id);
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $this->setHash($entry);

        if (!is_null($id) && $oldEntry->value !== $entry->value) {
            $data = [
                "account_from" => $oldEntry->value > $entry->value ? $id : null,
                "account_to" => $oldEntry->value > $entry->value ? null : $id,
                "value" => abs($oldEntry->value - $entry->value)
            ];
            $payload = $this->transaction_writer->save(null, $data, ["is_account_based_save" => true]);
        }

        return $payload;
    }

    public function getObjectViewRoute(): string
    {
        return 'finances_account_edit';
    }

    public function getObjectViewRouteParams($entry): array
    {
        return ["id" => $entry->id];
    }

    public function getModule(): string
    {
        return "finances";
    }
}
