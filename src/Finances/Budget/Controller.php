<?php

namespace App\Finances\Budget;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Main\UserHelper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\Budget\Budget';
    protected $index_route = 'finances_budgets';
    protected $element_view_route = 'finances_budgets_edit';
    protected $module = "finances";
    private $cat_mapper;
    private $recurring_mapper;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, UserHelper $user_helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation) {
        parent::__construct($logger, $twig, $helper, $user_helper, $flash, $router, $settings, $db, $activity, $translation);

        $user = $this->user_helper->getUser();

        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->db, $this->translation, $user);
        $this->recurring_mapper = new \App\Finances\Recurring\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {

        $categories = $this->cat_mapper->getAll('name');

        $budgets_with_data = $this->mapper->getBudgets('description');

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


        return $this->twig->render($response, 'finances/budget/index.twig', [
                    'budgets' => $budgets,
                    'categories' => $categories,
                    'currency' => $this->settings->getAppSettings()['i18n']['currency'],
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

        $this->sortBudgets($budgets);

        return $this->twig->render($response, 'finances/budget/edit.twig', [
                    'budgets' => $budgets,
                    'categories' => $categories,
                    'income' => $income,
                    'recurring' => $recurring,
                    'currency' => $this->settings->getAppSettings()['i18n']['currency'],
                    'hasRemainsBudget' => $has_remains_budget,
                    'budget_sum' => $budget_sum,
                    'budget_categories' => $budget_categories,
        ]);
    }

    public function saveAll(Request $request, Response $response) {

        $data = $request->getParsedBody();
        $user = $this->user_helper->getUser()->id;

        if (array_key_exists("budget", $data) && is_array($data["budget"])) {

            foreach ($data["budget"] as $budget_entry) {
                $budget_entry["user"] = $user;
                $bid = array_key_exists("id", $budget_entry) ? filter_var($budget_entry['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                $this->insertOrUpdate($bid, $budget_entry, $request);
            }
        }

        return $response->withRedirect($this->router->urlFor($this->index_route), 301);
    }

    public function getCategoryCosts(Request $request, Response $response) {

        $category = $request->getQueryParam('category');

        if (is_null($category)) {
            $response_data = ['status' => 'error', "error" => "empty"];
            return $response->withJSON($response_data);
        }

        try {
            $categories = filter_var_array($category, FILTER_SANITIZE_NUMBER_INT);
            $sum = $this->recurring_mapper->getSumOfCategories($categories);
        } catch (\Exception $e) {
            $this->logger->addError("Get Category Costs", array("data" => $category, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'success', 'value' => $sum];
        return $response->withJSON($response_data);
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

    /**
     * Save categories in m:n table
     */
    protected function afterSave($id, array $data, Request $request) {
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
            $this->logger->addError("Save Categories at Budget", array("data" => $id, "error" => $e->getMessage()));

            $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR"));
            $this->flash->addMessage('message_type', 'danger');
        }
    }

}
