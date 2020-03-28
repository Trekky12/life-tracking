<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\ObjectActivityRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class PaymethodRemover extends ObjectActivityRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, PaymethodMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function delete($id, $user = null): Payload {
        return parent::delete($id, null);
    }

    public function getObjectViewRoute(): string {
        return 'finances_paymethod_edit';
    }

    public function getObjectViewRouteParams(int $id): array {
        return ["id" => $id];
    }

    public function getModule(): string {
        return "finances";
    }

}
