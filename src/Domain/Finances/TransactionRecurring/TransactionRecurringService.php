<?php

namespace App\Domain\Finances\TransactionRecurring;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountService;
use App\Domain\Finances\TransactionRecurring\TransactionRecurring;

class TransactionRecurringService extends Service
{

    private $account_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        TransactionRecurringMapper $mapper,
        AccountService $account_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->account_service = $account_service;
    }

    public function index()
    {
        $list = $this->mapper->getAllWithNext();
        $accounts = $this->account_service->getAllAccountsOrderedByName();
        return new Payload(Payload::$RESULT_HTML, [
            'list' => $list,
            'units' => TransactionRecurring::getUnits(),
            'accounts' => $accounts
        ]);
    }

    public function edit($entry_id)
    {
        $entry = $this->getEntry($entry_id);

        $accounts = $this->account_service->getAllAccountsOrderedByName();

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'units' => TransactionRecurring::getUnits(),
            'accounts' => $accounts
        ]);
    }
}
