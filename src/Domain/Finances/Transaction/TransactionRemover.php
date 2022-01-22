<?php

namespace App\Domain\Finances\Transaction;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountMapper;

class TransactionRemover extends ObjectActivityRemover
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

    public function delete($id, $additionalData = null): Payload
    {
        $is_finance_entry_based_delete = is_array($additionalData) && array_key_exists("is_finance_entry_based_delete", $additionalData) && $additionalData["is_finance_entry_based_delete"];
        $is_bill_based_delete = is_array($additionalData) && array_key_exists("is_bill_based_delete", $additionalData) && $additionalData["is_bill_based_delete"];

        $error = null;
        try {
            $entry = $this->mapper->get($id);

            if (!is_null($entry) && ((!is_null($entry->finance_entry) && !$is_finance_entry_based_delete) || (!is_null($entry->bill_entry) && !$is_bill_based_delete))) {
                return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
            }

            if (!is_null($entry->account_from)) {
                $this->account_mapper->addValue($entry->account_from, $entry->value);
            }
            if (!is_null($entry->account_to)) {
                $this->account_mapper->substractValue($entry->account_to, $entry->value);
            }

            return parent::delete($id, $additionalData);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error("Delete failed " . $this->getMapper()->getDataObject(), array("id" => $id, "error" => $e->getMessage()));
        }
        return new Payload(Payload::$STATUS_ERROR, $error);
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
