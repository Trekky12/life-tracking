<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Budget\BudgetService;
use App\Domain\Finances\FinancesService;

class PaymethodWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, PaymethodMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        $payload = parent::save($id, $data, $user);
        $entry = $payload->getResult();

        // set default paymethod
        $this->setDefaultPaymethodWhenNotSet($entry->id);

        return $payload;
    }

    private function setDefaultPaymethodWhenNotSet($id) {

        $method = $this->mapper->get($id);

        // Set all other non-default, since there can only be one default category
        if ($method->is_default == 1) {
            $this->mapper->unset_default($id);
        }

        // when there is no default make this the default
        $default = $this->mapper->get_default();
        if (is_null($default)) {
            $this->mapper->set_default($id);
        }
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
