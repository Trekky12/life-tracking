<?php

namespace App\Domain\Finances\TransactionRecurring;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class TransactionRecurringRemover extends ObjectActivityRemover
{

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ActivityCreator $activity,
        TransactionRecurringMapper $mapper
    ) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function delete($id, $additionalData = null): Payload
    {
        return parent::delete($id, $additionalData);
    }

    public function getObjectViewRoute(): string
    {
        return 'finances_transaction_recurring_edit';
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
