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
use App\Domain\Finances\Transaction\TransactionRemover;
use App\Domain\Main\Translator;

class FinancesWriter extends ObjectActivityWriter {

    private $finances_service;
    private $budget_service;
    private $paymethod_service;
    private $transaction_writer;
    private $transaction_mapper;
    private $transaction_remover;
    private $translation;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        FinancesMapper $mapper,
        FinancesService $finances_service,
        BudgetService $budget_service,
        PaymethodService $paymethod_service,
        TransactionWriter $transaction_writer,
        TransactionMapper $transaction_mapper,
        TransactionRemover $transaction_remover,
        Translator $translation
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->budget_service = $budget_service;
        $this->paymethod_service = $paymethod_service;
        $this->transaction_writer = $transaction_writer;
        $this->transaction_mapper = $transaction_mapper;
        $this->transaction_remover = $transaction_remover;
        $this->translation = $translation;
    }

    public function save($id, $data, $additionalData = null): Payload {
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

        $is_paymethod_selectable = !is_null($entry) ? $this->finances_service->isPaymethodSelectable($entry->id) : true;

        if (!is_null($entry->paymethod) && $is_paymethod_editable) {

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
                    "id" => !is_null($entry->transaction) ? $entry->transaction : null,
                    "is_round_up_savings" => 0
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

                /**
                 * Round up savings
                 */
                if (!is_null($paymethod->round_up_savings_account) && $paymethod->round_up_savings > 0 && $entry->type == 0) {

                    $value = is_null($entry->bill) ? $entry->value : $entry->bill_paid;
                    $saving = (ceil($value / $paymethod->round_up_savings) * $paymethod->round_up_savings) - $value;

                    if ($saving > 0) {
                        $data2 = [
                            "date" => $entry->date,
                            "time" => $entry->time,
                            "value" => $saving,
                            "account_from" => $paymethod->account,
                            "account_to" => $paymethod->round_up_savings_account,
                            "description" => sprintf("%s %s", $this->translation->getTranslatedString("FINANCES_ROUND_UP_SAVINGS"), $entry->description),
                            "user" => $entry->user,
                            "finance_entry" => $entry->id,
                            "bill_entry" => !is_null($entry->bill) ? $entry->bill : null,
                            "id" => !is_null($entry->transaction_round_up_savings) ? $entry->transaction_round_up_savings : null,
                            "is_round_up_savings" => 1
                        ];

                        $transaction_round_up_savings_payload = $this->transaction_writer->save($data2["id"], $data2, ["is_finance_entry_based_save" => true]);
                        $transaction_round_up_savings_entry = $transaction_round_up_savings_payload->getResult();
                        $this->getMapper()->set_transaction_round_up_savings($entry->id, $transaction_round_up_savings_entry->id);
                        $entry->transaction_round_up_savings = $transaction_round_up_savings_entry->id;
                    }
                }
            }

            /**
             * If paymethod was changed and the new paymethod doesn't have round up saving, delete the corresponding transaction
             */
            if ($paymethod->round_up_savings == 0 && !is_null($entry->transaction_round_up_savings)) {
                $this->transaction_remover->delete($entry->transaction_round_up_savings, ["is_finance_entry_based_delete" => true]);
                $this->getMapper()->set_transaction_round_up_savings($entry->id, null);
                $entry->transaction_round_up_savings = null;
            }
        }
        /**
         * No Paymethod but maybe there was one before? 
         * So delete a possible transaction
         */
        else {
            if (!is_null($entry->transaction)) {
                $this->transaction_remover->delete($entry->transaction, ["is_finance_entry_based_delete" => true]);
                $this->getMapper()->set_transaction($entry->id, null);
                $entry->transaction = null;
            }
            if (!is_null($entry->transaction_round_up_savings)) {
                $this->transaction_remover->delete($entry->transaction_round_up_savings, ["is_finance_entry_based_delete" => true]);
                $this->getMapper()->set_transaction_round_up_savings($entry->id, null);
                $entry->transaction_round_up_savings = null;
            }
        }

        /**
         * Reset user back to initial!
         */
        if (!is_null($me)) {
            $this->current_user->setUser($me);
            $this->transaction_mapper->setUser($me->id);
        }


        return $payload;
    }

    private function setDefaultOrAssignedCategory($entry) {
        $cat = $this->finances_service->getDefaultOrAssignedCategory($entry);
        if (!is_null($cat)) {
            $this->getMapper()->set_category($entry->id, $cat);

            return $cat;
        }

        return null;
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
