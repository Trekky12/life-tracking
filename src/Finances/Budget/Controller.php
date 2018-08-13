<?php

namespace App\Finances\Budget;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $cat_mapper;
    private $recurring_mapper;

    public function init() {
        $this->model = '\App\Finances\Budget\Budget';
        $this->index_route = 'finances_budgets';

        $this->mapper = new Mapper($this->ci);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->ci);
        $this->recurring_mapper = new \App\Finances\Recurring\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $categories = $this->cat_mapper->getAll('name');

        $budgets = $this->mapper->getBudgets('description');

        $remains = $this->mapper->getRemainsBudget();
        if ($remains) {
            $remains->sum = $this->mapper->getRemainsExpenses();
            $remains->diff = $remains->value - $remains->sum;
            $remains->percent = round((($remains->sum / $remains->value) * 100), 2);
            array_push($budgets, $remains);
        }

        $budget_categories = $this->mapper->getBudgetCategories();

        // Current day of month
        $date = new \DateTime('now');
        // Current Day of Month / Count Days of Month
        $date_status = round($date->format('j') / $date->format('t') * 100, 2);


        return $this->ci->view->render($response, 'finances/budget/index.twig', [
                    'budgets' => $budgets,
                    'categories' => $categories,
                    'currency' => $this->ci->get('settings')['app']['i18n']['currency'],
                    'budget_categories' => $budget_categories,
                    'date_status' => $date_status
        ]);
    }

    public function edit(Request $request, Response $response) {
        $budgets = $this->mapper->getAll('description');
        $budget_categories = $this->mapper->getBudgetCategories();

        $categories = $this->cat_mapper->getAll('name');

        $recurring = $this->recurring_mapper->getSumOfAllCategories();
        $income = $this->recurring_mapper->getSum(1);
        $budget_sum = $this->mapper->getSum();
        $has_remains_budget = $this->mapper->hasRemainsBudget();

        $this->sortBudgets($budgets, $budget_categories);

        return $this->ci->view->render($response, 'finances/budget/edit.twig', [
                    'budgets' => $budgets,
                    'categories' => $categories,
                    'income' => $income,
                    'recurring' => $recurring,
                    'currency' => $this->ci->get('settings')['app']['i18n']['currency'],
                    'hasRemainsBudget' => $has_remains_budget,
                    'budget_sum' => $budget_sum,
                    'budget_categories' => $budget_categories,
        ]);
    }

    public function saveAll(Request $request, Response $response) {

        $data = $request->getParsedBody();
        $user = $this->ci->get('helper')->getUser()->id;

        if (array_key_exists("budget", $data) && is_array($data["budget"])) {

            foreach ($data["budget"] as $budget_entry) {
                $budget_entry["user"] = $user;
                $bid = array_key_exists("id", $budget_entry) ? filter_var($budget_entry['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                $this->insertOrUpdate($bid, $budget_entry);
            }
        }

        return $response->withRedirect($this->ci->get('router')->pathFor($this->index_route), 301);
    }

    public function getCategoryCosts(Request $request, Response $response) {

        $category = $request->getQueryParam('category');

        if (is_null($category)) {
            return $response->withJSON(array('status' => 'error', "error" => "empty"));
        }

        try {
            $categories = filter_var_array($category, FILTER_SANITIZE_NUMBER_INT);
            $sum = $this->recurring_mapper->getSumOfCategories($categories);
        } catch (\Exception $e) {
            return $response->withJSON(array('status' => 'error', "error" => $e->getMessage()));
        }

        return $response->withJSON(array('status' => 'success', 'value' => $sum));
    }

    /**
     * Sort Remaining entry to end of list
     */
    private function sortBudgets(&$budgets, $budget_categories) {

        $remaining = null;
        foreach ($budgets as $idx => $budget) {
            if (!array_key_exists($budget->id, $budget_categories)) {
                $remaining = $budget;
                unset($budgets[$idx]);
            }
        }
        if (!is_null($remaining)) {
            array_push($budgets, $remaining);
        }
    }

    /**
     * Save categories in m:n table
     */
    protected function afterSave($id, $data) {
        try {
            // remove old categories
            $this->mapper->deleteCategoriesFromBudget($id);

            // add new categories
            if (array_key_exists("category", $data) && is_array($data["category"])) {
                $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);
                $this->mapper->addCategoriesToBudget($id, $categories);
            }
        } catch (\Exception $e) {
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("ENTRY_ERROR"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');
        }
    }

}
