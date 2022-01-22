<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Account\AccountService;

class PaymethodService extends Service {

    private $account_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, PaymethodMapper $mapper, AccountService $account_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->account_service = $account_service;
    }

    public function getAllPaymethodsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getAllfromUsers($group_users) {
        return $this->mapper->getAllfromUsers($group_users);
    }

    public function index() {
        $paymethods = $this->getAllPaymethodsOrderedByName();
        $accounts = $this->account_service->getAllAccountsOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['paymethods' => $paymethods, 'accounts' => $accounts]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        $accounts = $this->account_service->getAllAccountsOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'accounts' => $accounts]);
    }

    public function getPaymethodOfUser($id, $user_id){
        return $this->mapper->getofUser($id, $user_id);
    }

}
