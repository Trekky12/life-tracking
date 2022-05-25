<?php

namespace App\Domain\Finances\Account;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class AccountService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user, AccountMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function getAllAccountsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getAllfromUsers($group_users) {
        return $this->mapper->getAllfromUsers($group_users);
    }

    public function index() {
        $accounts = $this->getAllAccountsOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['accounts' => $accounts]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
