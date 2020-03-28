<?php

namespace App\Domain\Finances\Budget;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Recurring\RecurringMapper;

class BudgetService extends \App\Domain\Service {

    private $cat_service;
    private $recurring_mapper;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            BudgetMapper $mapper,
            CategoryService $cat_service,
            RecurringMapper $recurring_mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->cat_service = $cat_service;
        $this->recurring_mapper = $recurring_mapper;
    }

    public function getAllBudgetsOrderedByDescription() {
        return $this->mapper->getBudgets('description');
    }

    public function budgets() {
        $budgets_with_data = $this->getAllBudgetsOrderedByDescription();

        $remains = $this->mapper->getRemainsBudget();
        if ($remains) {
            $remains->sum = $this->mapper->getRemainsExpenses();
            $remains->diff = $remains->value - $remains->sum;
            $remains->percent = round((($remains->sum / $remains->value) * 100), 2);
            $budgets_with_data[$remains->id] = $remains;
        }

        // add missing budgets (when there are no finance entries)
        $all_budgets = $this->mapper->getAll('description');

        $budgets = [];
        foreach ($all_budgets as $budget_id => $ab) {
            if (array_key_exists($budget_id, $budgets_with_data)) {
                $budgets[] = $budgets_with_data[$budget_id];
            } else {
                $budgets[] = $ab;
            }
        }

        $this->sortBudgets($budgets);

        $budget_categories = $this->mapper->getBudgetCategories();

        // Current day of month
        $date = new \DateTime('now');
        // Current Day of Month / Count Days of Month
        $date_status = round($date->format('j') / $date->format('t') * 100, 2);

        return [
            'budgets' => $budgets,
            'currency' => $this->settings->getAppSettings()['i18n']['currency'],
            'budget_categories' => $budget_categories,
            'date_status' => $date_status
        ];
    }

    /**
     * Sort Remaining entry to end of list
     */
    private function sortBudgets(&$budgets) {

        $remaining = null;
        foreach ($budgets as $idx => $budget) {
            if ($budget->is_remaining()) {
                $remaining = $budget;
                unset($budgets[$idx]);
            }
        }
        if (!is_null($remaining)) {
            array_push($budgets, $remaining);
        }
    }

    public function get($budget) {
        return $this->mapper->get($budget);
    }

    public function isRemainsBudget($budget) {
        return $this->mapper->isRemainsBudget($budget);
    }

    public function getCategoriesFromBudget($budget) {
        return $this->mapper->getCategoriesFromBudget($budget);
    }

    public function checkBudget(FinancesEntry $entry) {
        $results = array();

        $date = new \DateTime($entry->date);
        $now = new \DateTime('now');

        if (($date->format('m') == $now->format('m')) && $entry->type == 0) {

            $budgets = $this->mapper->getBudgetsFromCategory($entry->category);
            $all_budgets = $this->mapper->getBudgets();

            // remains
            if (empty($budgets)) {
                $remains = $this->mapper->getRemainsBudget();
                if ($remains) {
                    $remains->sum = $this->mapper->getRemainsExpenses();
                    $remains->diff = $remains->value - $remains->sum;
                    $remains->percent = round((($remains->sum / $remains->value) * 100), 2);

                    $type = 'success';
                    if ($remains->percent > 80) {
                        $type = 'danger';
                    } elseif ($remains->percent > 50) {
                        $type = 'warning';
                    }

                    //$message = $this->translation->getTranslatedString("REMAINING_BUDGET") . " (" . $remains->description . "): " . $remains->diff . " " . $this->settings->getAppSettings()['i18n']['currency'];
                    $message = $this->translation->getTranslatedString("BUDGET") . " (" . $remains->description . "): " . $remains->percent . "%";

                    array_push($results, array('message' => $message, 'type' => $type));
                }
            } else {
                // Budget of category:
                foreach ($budgets as $budget) {
                    $type = 'success';
                    if ($all_budgets[$budget->id]->percent > 80) {
                        $type = 'danger';
                    } elseif ($all_budgets[$budget->id]->percent > 50) {
                        $type = 'warning';
                    }
                    //$message = $this->translation->getTranslatedString("REMAINING_BUDGET") . " (" . html_entity_decode($all_budgets[$budget->id]->description) . "): " . $all_budgets[$budget->id]->diff . " " . $this->settings->getAppSettings()['i18n']['currency'];
                    $message = $this->translation->getTranslatedString("BUDGET") . " (" . html_entity_decode($all_budgets[$budget->id]->description) . "): " . $all_budgets[$budget->id]->percent . "%";

                    array_push($results, array('message' => $message, 'type' => $type));
                }
            }
        }
        return $results;
    }

    public function index() {
        $budgets = $this->budgets();
        $budgets['categories'] = $this->cat_service->getAllCategoriesOrderedByName();

        return $budgets;
    }

    public function edit() {
        $categories = $this->cat_service->getAllCategoriesOrderedByName();

        $recurring = $this->recurring_mapper->getSumOfAllCategories();
        $income = $this->recurring_mapper->getSum(1);

        $budgets = $this->mapper->getAll('description');
        $budget_categories = $this->mapper->getBudgetCategories();
        $budget_sum = $this->mapper->getSum();
        $has_remains_budget = $this->mapper->hasRemainsBudget();

        $this->sortBudgets($budgets);

        $currency = $this->settings->getAppSettings()['i18n']['currency'];

        return [
            'budgets' => $budgets,
            'categories' => $categories,
            'income' => $income,
            'recurring' => $recurring,
            'currency' => $currency,
            'hasRemainsBudget' => $has_remains_budget,
            'budget_sum' => $budget_sum,
            'budget_categories' => $budget_categories,
        ];
    }

}
