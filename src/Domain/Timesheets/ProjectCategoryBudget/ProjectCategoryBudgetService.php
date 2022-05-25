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

class ProjectCategoryBudgetService extends Service {

    private $project_service;
    protected $project_category_service;
    protected $translation;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            ProjectCategoryBudgetMapper $mapper,
            ProjectService $project_service,
            ProjectCategoryService $project_category_service,
            Translator $translation) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->project_service = $project_service;
        $this->project_category_service = $project_category_service;
        $this->translation = $translation;
    }

    public function index($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $categorybudgets = $this->mapper->getFromProject($project->id);

        return new Payload(Payload::$RESULT_HTML, ['categorybudgets' => $categorybudgets, "project" => $project]);
    }

    public function edit($hash, $entry_id) {

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

        return new Payload(Payload::$RESULT_HTML, [
            "entry" => $entry,
            "project" => $project,
            "categories" => $project_categories,
            "categorybudget_categories" => $categorybudget_categories
        ]);
    }

    public function view($hash) {

        $project = $this->project_service->getFromHash($hash);

        if (!$this->project_service->isMember($project->id)) {
            return new Payload(Payload::$NO_ACCESS, "NO_ACCESS");
        }

        $categorybudgets = $this->mapper->getBudgetForCategories($project->id);

        // group by main category
        $budgets = [];
        foreach ($categorybudgets as $cat_budget) {
            if (!array_key_exists($cat_budget["main_category"], $budgets)) {
                $budgets[$cat_budget["main_category"]] = ["name" => $cat_budget["main_category_name"], "items" => []];
            }
            $budgets[$cat_budget["main_category"]]["items"][] = $cat_budget;
        }

        //$test = $this->mapper->getBudgetForCategories($project->id, []);

        return new Payload(Payload::$RESULT_HTML, [
            "categorybudgets" => $budgets,
            "project" => $project
        ]);
    }

    public function checkCategoryBudgets($project_id, $categories, $sheet_id) {

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
