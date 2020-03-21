<?php

namespace App\Finances\Budget;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Finances\Category\CategoryService;
use App\Finances\Recurring\RecurringService;

class Controller extends \App\Base\Controller {

    private $cat_service;
    private $recurring_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            BudgetService $service,
            CategoryService $cat_service,
            RecurringService $recurring_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->cat_service = $cat_service;
        $this->recurring_service = $recurring_service;
    }

    public function index(Request $request, Response $response) {

        $budgets = $this->service->budgets();
        $budgets['categories'] = $this->cat_service->getAllCategoriesOrderedByName();

        return $this->twig->render($response, 'finances/budget/index.twig', $budgets);
    }

    public function edit(Request $request, Response $response) {

        $categories = $this->cat_service->getAllCategoriesOrderedByName();

        $recurring = $this->recurring_service->getSumOfAllCategories();
        $income = $this->recurring_service->getSumIncome();

        list($budgets, $budget_categories, $budget_sum, $has_remains_budget, $currency) = $this->service->edit();

        return $this->twig->render($response, 'finances/budget/edit.twig', [
                    'budgets' => $budgets,
                    'categories' => $categories,
                    'income' => $income,
                    'recurring' => $recurring,
                    'currency' => $currency,
                    'hasRemainsBudget' => $has_remains_budget,
                    'budget_sum' => $budget_sum,
                    'budget_categories' => $budget_categories,
        ]);
    }

    public function saveAll(Request $request, Response $response) {

        $data = $request->getParsedBody();

        if (array_key_exists("budget", $data) && is_array($data["budget"])) {

            foreach ($data["budget"] as $budget_entry) {
                $bid = array_key_exists("id", $budget_entry) ? filter_var($budget_entry['id'], FILTER_SANITIZE_NUMBER_INT) : null;

                $entry = $this->service->createEntry($budget_entry);

                if (is_null($bid)) {
                    $this->service->insertEntry($entry);
                } else {
                    $this->service->updateEntry($entry);
                }

                $this->addCategories($bid, $budget_entry);
            }
        }

        return $response->withRedirect($this->router->urlFor('finances_budgets'), 301);
    }

    private function addCategories($id, $data) {
        try {
            $categories = null;
            if (array_key_exists("category", $data) && is_array($data["category"])) {
                $categories = filter_var_array($data["category"], FILTER_SANITIZE_NUMBER_INT);
            }

            if (!is_null($categories)) {
                $this->service->addCategories($id, $categories);
            }
        } catch (\Exception $e) {
            $this->logger->addError("Save Categories at Budget", array("data" => $id, "error" => $e->getMessage()));

            $this->flash->addMessage('message', $this->translation->getTranslatedString("ENTRY_ERROR"));
            $this->flash->addMessage('message_type', 'danger');
        }
    }

    public function getCategoryCosts(Request $request, Response $response) {

        $category = $request->getQueryParam('category');

        if (is_null($category)) {
            $response_data = ['status' => 'error', "error" => "empty"];
            return $response->withJSON($response_data);
        }

        try {
            $categories = filter_var_array($category, FILTER_SANITIZE_NUMBER_INT);
            $sum = $this->recurring_service->getSumOfCategories($categories);
        } catch (\Exception $e) {
            $this->logger->addError("Get Category Costs", array("data" => $category, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return $response->withJSON($response_data);
        }

        $response_data = ['status' => 'success', 'value' => $sum];
        return $response->withJSON($response_data);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $response_data = $this->doDelete($id);
        return $response->withJson($response_data);
    }

}
