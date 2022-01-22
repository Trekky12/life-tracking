<?php

namespace App\Domain\Finances;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Budget\BudgetService;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\Transaction\TransactionWriter;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Domain\Finances\Transaction\TransactionMapper;

class FinancesWriter extends ObjectActivityWriter
{

    private $finances_service;
    private $budget_service;
    private $paymethod_service;
    private $transaction_writer;
    private $transaction_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        FinancesMapper $mapper,
        FinancesService $finances_service,
        BudgetService $budget_service,
        PaymethodService $paymethod_service,
        TransactionWriter $transaction_writer,
        TransactionMapper $transaction_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->budget_service = $budget_service;
        $this->paymethod_service = $paymethod_service;
        $this->transaction_writer = $transaction_writer;
        $this->transaction_mapper = $transaction_mapper;
    }

    public function save($id, $data, $additionalData = null): Payload
    {
        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        if (in_array($payload->getStatus(), [Payload::$STATUS_NEW, Payload::$STATUS_UPDATE])) {
            // set default or assigned category
            $category = $this->setDefaultOrAssignedCategory($entry);
            if (!is_null($category)) {
                $entry->category = $category;
            }

            // Check Budget
            $budget_result = $this->budget_service->checkBudget($entry);
            foreach ($budget_result as $result) {
                $payload->addFlashMessage('additional_flash_message_type', $result["type"]);
                $payload->addFlashMessage('additional_flash_message', $result["message"]);
            }
        }

        /**
         * Create Transaction
         */
        // Before creating/deleting a transaction entry the user is checked
        // for access rights, but when an entry for another user is created the 
        // current user has no access rights
        // So the current user needs to be set to the
        // finance entry creator        
        $me = $this->current_user->getUser();
        $this->current_user->setUser(null);
        $this->transaction_mapper->setUser($entry->user);

        if (!is_null($entry->paymethod)) {

            $paymethod = $this->paymethod_service->getPaymethodOfUser($entry->paymethod, $entry->user);

            if (!is_null($paymethod->account)) {

                $data = [
                    "date" => $entry->date,
                    "time" => $entry->time,
                    "value" => is_null($entry->bill) ? $entry->value : $entry->bill_paid,
                    "account_from" => null,
                    "account_to" => null,
                    "description" => $entry->description,
                    "user" => $entry->user,
                    "finance_entry" => $entry->id,
                    "bill_entry" => !is_null($entry->bill) ? $entry->bill : null,
                    "id" => !is_null($entry->transaction) ? $entry->transaction : null
                ];

                if ($entry->type == 0) {
                    $data["account_from"] = $paymethod->account;
                } else {
                    $data["account_to"] = $paymethod->account;
                }

                $transaction_payload = $this->transaction_writer->save($data["id"], $data, ["is_finance_entry_based_save" => true]);
                $transaction_entry = $transaction_payload->getResult();
                $this->getMapper()->set_transaction($entry->id, $transaction_entry->id);
                $entry->transaction = $transaction_entry->id;
            }
        }
        // Reset user back to initial!
        if(!is_null($me)){
            $this->current_user->setUser($me);
            $this->transaction_mapper->setUser($me->id);
        }


        return $payload;
    }

    private function setDefaultOrAssignedCategory($entry)
    {
        $cat = $this->finances_service->getDefaultOrAssignedCategory($entry);
        if (!is_null($cat)) {
            $this->getMapper()->set_category($entry->id, $cat);

            return $cat;
        }

        return null;
    }

    public function getObjectViewRoute(): string
    {
        return 'finances_edit';
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
