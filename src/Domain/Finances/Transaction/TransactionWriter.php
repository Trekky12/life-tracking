<?php

namespace App\Domain\Finances\Transaction;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountMapper;

class TransactionWriter extends ObjectActivityWriter
{

    private $account_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        TransactionMapper $mapper,
        AccountMapper $account_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->account_mapper = $account_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload
    {

        $oldValue = 0;
        if (!is_null($id)) {
            $entry = $this->mapper->get($id);
            $oldValue = $entry->value;

            $is_finance_entry_based_save = is_array($additionalData) && array_key_exists("is_finance_entry_based_save", $additionalData) && $additionalData["is_finance_entry_based_save"];
            $is_bill_based_save = is_array($additionalData) && array_key_exists("is_bill_based_save", $additionalData) && $additionalData["is_bill_based_save"];

            if (!is_null($entry) && ((!is_null($entry->finance_entry) && !$is_finance_entry_based_save) || (!is_null($entry->bill_entry) && !$is_bill_based_save))) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $difference = abs($oldValue - $entry->value);

        $is_account_based_save = is_array($additionalData) && array_key_exists("is_account_based_save", $additionalData) && $additionalData["is_account_based_save"];
        if (!is_null($entry->account_from) && !$is_account_based_save) {
            if ($entry->value > $oldValue) {
                $this->account_mapper->substractValue($entry->account_from, $difference);
            } else {
                $this->account_mapper->addValue($entry->account_from, $difference);
            }
        }
        if (!is_null($entry->account_to) && !$is_account_based_save) {
            if ($entry->value > $oldValue) {
                $this->account_mapper->addValue($entry->account_to, $difference);
            } else {
                $this->account_mapper->substractValue($entry->account_to, $difference);
            }
        }

        if (array_key_exists("account", $additionalData) && !empty($additionalData["account"])) {

            try {
                $account = $this->account_mapper->getFromHash($additionalData["account"]);

                $payload = $payload->withAdditionalData(["account" => $account]);
            } catch (\Exception $e) {
            }
        }

        return $payload;
    }

    public function getObjectViewRoute(): string
    {
        return 'finances_transaction_view';
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
