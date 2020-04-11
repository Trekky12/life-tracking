<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class PaymethodService extends Service {

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
        return new Payload(Payload::$RESULT_HTML, ['paymethods' => $paymethods]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry]);
    }

}
