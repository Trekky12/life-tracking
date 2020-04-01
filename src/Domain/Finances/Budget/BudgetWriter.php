<?php

namespace App\Domain\Finances\Budget;

use App\Domain\ObjectActivityWriter;
use Psr\Log\LoggerInterface;
use App\Domain\Activity\ActivityCreator;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class BudgetWriter extends ObjectActivityWriter {

    public function __construct(LoggerInterface $logger, CurrentUser $user, ActivityCreator $activity, BudgetMapper $mapper) {
        parent::__construct($logger, $user, $activity);
        $this->mapper = $mapper;
    }

    public function save($id, $data, $user = null): Payload {
        $payloads = [];
        if (array_key_exists("budget", $data) && is_array($data["budget"])) {

            foreach ($data["budget"] as $budget_entry) {
                $bid = array_key_exists("id", $budget_entry) ? filter_var($budget_entry['id'], FILTER_SANITIZE_NUMBER_INT) : null;

                $payload = parent::save($bid, $budget_entry);
                $payloads[] = $payload;
                $entry = $payload->getResult();

                $this->addCategories($entry->id, $budget_entry);
            }
        }
        return new Payload(Payload::$RESULT_ARRAY, $payloads);
    }

    private function addCategories($id, $data) {
        try {
            $categories = null;
            if (array_key_exists("category", $data) && is_array($data["category"])) {
                $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);
            }

            if (!is_null($categories)) {
                // remove old categories
                $this->mapper->deleteCategoriesFromBudget($id);
                // add new categories
                $this->mapper->addCategoriesToBudget($id, $categories);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Error while saving categories at budget", array("data" => $id, "error" => $e->getMessage()));
        }
    }

    public function getObjectViewRoute(): string {
        return 'finances_budgets_edit';
    }

    public function getObjectViewRouteParams($entry): array {
        return ["id" => $entry->id];
    }

    public function getModule(): string {
        return "finances";
    }

}
