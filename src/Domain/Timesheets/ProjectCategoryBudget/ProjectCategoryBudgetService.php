<?php

namespace App\Domain\Timesheets\ProjectCategoryBudget;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\ProjectCategory\ProjectCategoryService;
use App\Domain\Main\Translator;
use App\Domain\Main\Utility\DateUtility;
use App\Domain\Timesheets\Customer\CustomerService;

class ProjectCategoryBudgetService extends Service
{

    private $project_service;
    protected $project_category_service;
    protected $translation;
    protected $customer_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        ProjectCategoryBudgetMapper $mapper,
        ProjectService $project_service,
        ProjectCategoryService $project_category_service,
        Translator $translation,
        CustomerService $customer_service
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_category_service = $project_category_service;
        $this->translation = $translation;
        $this->customer_service = $customer_service;
    }

    public function index($hash)
    {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $categorybudgets = $this->mapper->getFromProject($project->id);
        $customers = $this->customer_service->getCustomersFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, ['categorybudgets' => $categorybudgets, "project" => $project, 'customers' => $customers]);
    }

    public function edit($hash, $entry_id, $use_template = null)
    {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }
        if (!$this->isChildOf($project->id, $entry_id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $entry = $this->getEntry($entry_id);
        $project_categories = $this->project_category_service->getCategoriesFromProject($project->id);
        $categorybudget_categories = !is_null($entry) ? $this->mapper->getCategoriesFromCategoryBudget($entry->id) : [];
        $customers = $this->customer_service->getCustomersFromProject($project->id);

        if (is_null($entry) && !is_null($use_template)) {
            $template = $this->mapper->get($use_template);
            if ($this->isChildOf($project->id, $template->id)) {
                $categorybudget_categories = $this->mapper->getCategoriesFromCategoryBudget($template->id);
                $entry = $template->copy();
            }
        }

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "project" => $project,
            "categories" => $project_categories,
            "categorybudget_categories" => $categorybudget_categories,
            "customers" => $customers
        ]);
    }

    public function view($hash)
    {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $categorybudgets = $this->mapper->getBudgetForCategories($project->id);

        // group by customer and main category
        $budgets = [];
        foreach ($categorybudgets as $cat_budget) {
            $customer = !is_null($cat_budget["customer"]) ? $cat_budget["customer"] : "none";
            $main_category = !is_null($cat_budget["main_category"]) ? $cat_budget["main_category"] : "none";

            if (!array_key_exists($customer, $budgets)) {
                $budgets[$customer] = ["name" => $cat_budget["customer_name"], "items" => []];
            }
            if (!array_key_exists($main_category, $budgets[$customer]["items"])) {
                $budgets[$customer]["items"][$main_category] = ["name" => $cat_budget["main_category_name"], "items" => []];
            }
            $budgets[$customer]["items"][$main_category]["items"][] = $cat_budget;
        }

        //$test = $this->mapper->getBudgetForCategories($project->id, []);

        return new Payload(Payload::$RESULT_HTML, [
            "categorybudgets" => $budgets,
            "project" => $project
        ]);
    }

    public function checkCategoryBudgets($project_id, $categories, $sheet_id)
    {

        $results = [];

        $budgets = $this->mapper->getBudgetForCategories($project_id, $categories, $sheet_id);

        foreach ($budgets as $budget) {

            if ($budget["warning3"] && $budget["sum"] >= $budget["warning3"]) {
                $type = 'warning3';
            } elseif ($budget["warning2"] && $budget["sum"] >= $budget["warning2"]) {
                $type = 'warning2';
            } elseif ($budget["warning1"] && $budget["sum"] >= $budget["warning1"]) {
                $type = 'warning1';
            } else {
                $type = 'info';
            }
            $message = $this->translation->getTranslatedString("BUDGET") . " (" . html_entity_decode($budget["name"]) . "): " . $budget["percent"] . "%";

            $message .= " (" . (($budget["categorization"] != 'count') ? DateUtility::splitDateInterval($budget["sum"], true) . "/" . DateUtility::splitDateInterval($budget["value"], true) : $budget["sum"] . "/" . $budget["value"]);
            $message .= ", " . $this->translation->getTranslatedString("REMAINING") . ": " . (($budget["categorization"] != 'count') ? DateUtility::splitDateInterval($budget["diff"], true) : $budget["diff"]) . ")";

            // only budgets with this sheet
            if ($budget["sheet_in_budget"] > 0) {
                array_push($results, array('message' => $message, 'type' => $type));
            }
        }

        return $results;
    }
}
