<?php

namespace App\Domain\Finances;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\Transaction\TransactionRemover;
use App\Domain\Finances\Transaction\TransactionMapper;

class FinancesRemover extends ObjectActivityRemover
{

    private $finances_service;
    private $transaction_remover;
    private $transaction_mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        FinancesMapper $mapper,
        FinancesService $finances_service,
        TransactionRemover $transaction_remover,
        TransactionMapper $transaction_mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->transaction_remover = $transaction_remover;
        $this->transaction_mapper = $transaction_mapper;
    }

    public function delete($id, $additionalData = null): Payload
    {

        try {
            $is_splitted = $this->finances_service->isSplittedBillEntry($id);
            $is_splitted_bill_deletion = is_array($additionalData) && array_key_exists("is_bill_based_delete", $additionalData) && $additionalData["is_bill_based_delete"];
            if ($is_splitted && !$is_splitted_bill_deletion) {
                return new Payload(Payload::$STATUS_ERROR, 'NO_ACCESS');
            } else {

                // Get transaction of finance entry
                $entry = $this->mapper->get($id);

                /**
                 * Delete Transaction
                 */
                $me = $this->current_user->getUser();
                $this->current_user->setUser(null);
                $this->transaction_mapper->setUser($entry->user);
                if (!is_null($entry->transaction)) {
                    $this->transaction_remover->delete($entry->transaction, ["is_finance_entry_based_delete" => true]);
                }
                if (!is_null($entry->transaction_round_up_savings)) {
                    $this->transaction_remover->delete($entry->transaction_round_up_savings, ["is_finance_entry_based_delete" => true]);
                }
                // when deleting a bill, the user can already be null
                if(!is_null($me)){
                    $this->current_user->setUser($me);
                    $this->transaction_mapper->setUser($me->id);
                }

                return parent::delete($id, $additionalData);
            }
        } catch (\Exception $ex) {
        }
        return new Payload(Payload::$STATUS_ERROR, 'ELEMENT_NOT_FOUND');
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
