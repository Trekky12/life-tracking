<?php

namespace App\Domain\Finances;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Budget\BudgetService;
use App\Domain\Finances\FinancesService;

class FinancesWriter extends ObjectActivityWriter {

    private $finances_service;
    private $budget_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, FinancesMapper $mapper, FinancesService $finances_service, BudgetService $budget_service) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
        $this->finances_service = $finances_service;
        $this->budget_service = $budget_service;
    }

    public function save($id, $data, $additionalData = null): Payload {
        $payload = parent::save($id, $data, $additionalData);
        $entry = $payload->getResult();

        // set default or assigned category
        $this->setDefaultOrAssignedCategory($entry->id);
        // Check Budget
        $budget_result = $this->budget_service->checkBudget($entry);
        foreach ($budget_result as $result) {
            $payload->addFlashMessage('budget_message_type', $result["type"]);
            $payload->addFlashMessage('budget_message', $result["message"]);
        }
        return $payload;
    }

    private function setDefaultOrAssignedCategory($id) {
        $user_id = $this->current_user->getUser()->id;

        $entry = $this->getMapper()->get($id);
        $cat = $this->finances_service->getDefaultOrAssignedCategory($user_id, $entry);
        if (!is_null($cat)) {
            $this->getMapper()->set_category($id, $cat);
        }
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
