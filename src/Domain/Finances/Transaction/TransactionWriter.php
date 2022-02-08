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

        $oldEntry = null;
        if (!is_null($id)) {
            $oldEntry = $this->mapper->get($id);

            $is_finance_entry_based_save = is_array($additionalData) && array_key_exists("is_finance_entry_based_save", $additionalData) && $additionalData["is_finance_entry_based_save"];
            $is_bill_based_save = is_array($additionalData) && array_key_exists("is_bill_based_save", $additionalData) && $additionalData["is_bill_based_save"];

            /**
             * Do not allow manual editing of finance entry or bill based transactions
             */
            if (!is_null($oldEntry) && ((!is_null($oldEntry->finance_entry) && !$is_finance_entry_based_save) || (!is_null($oldEntry->bill_entry) && !$is_bill_based_save))) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }
        }

        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        $is_account_based_save = is_array($additionalData) && array_key_exists("is_account_based_save", $additionalData) && $additionalData["is_account_based_save"];
        if (!$is_account_based_save) {
            $this->updateAccount($oldEntry, $entry);
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

    private function updateAccount($oldEntry, $entry)
    {
        $oldValue = !is_null($oldEntry) ? $oldEntry->value : 0;

        /**
         * If account is updated, then we need to undo the changing of the account values
         */
        if (!is_null($oldEntry) && !is_null($oldEntry->account_from) && $oldEntry->account_from !== $entry->account_from) {
            $this->account_mapper->addValue($oldEntry->account_from, $oldEntry->value);
            $oldValue = 0;
        }
        if (!is_null($oldEntry) && !is_null($oldEntry->account_to) && $oldEntry->account_to !== $entry->account_to) {
            $this->account_mapper->substractValue($oldEntry->account_to, $oldEntry->value);
            $oldValue = 0;
        }

        /**
         * Set or update account values of entry
         */
        $difference = abs($oldValue - $entry->value);
        if (!is_null($entry->account_from)) {
            if ($entry->value > $oldValue) {
                $this->account_mapper->substractValue($entry->account_from, $difference);
            } else {
                $this->account_mapper->addValue($entry->account_from, $difference);
            }
        }
        if (!is_null($entry->account_to)) {
            if ($entry->value > $oldValue) {
                $this->account_mapper->addValue($entry->account_to, $difference);
            } else {
                $this->account_mapper->substractValue($entry->account_to, $difference);
            }
        }
    }
}
