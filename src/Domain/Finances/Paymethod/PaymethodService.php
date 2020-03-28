<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;

class PaymethodService extends GeneralService {

    public function __construct(LoggerInterface $logger, CurrentUser $user, PaymethodMapper $cat_mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $cat_mapper;
    }

    public function getAllPaymethodsOrderedByName() {
        return $this->mapper->getAll('name');
    }

    public function getAllfromUsers($group_users) {
        return $this->mapper->getAllfromUsers($group_users);
    }

    public function index() {
        $paymethods = $this->getAllPaymethodsOrderedByName();
        return ['paymethods' => $paymethods];
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return ['entry' => $entry];
    }

}
