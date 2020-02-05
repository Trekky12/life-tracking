<?php

namespace App\Finances;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\FigRequestCookies;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\FinancesEntry';
    protected $index_route = 'finances';
    protected $element_view_route = 'finances_edit';
    protected $module = "finances";
    
    private $cat_mapper;
    private $cat_assignments_mapper;
    private $budget_mapper;
    private $paymethod_mapper;
    
    static $GROUP_CATEGORIES_BUDGET_CHART = 5;

    public function init() {
        $this->mapper = new Mapper($this->ci);
        $this->cat_mapper = new Category\Mapper($this->ci);
        $this->cat_assignments_mapper = new Assignment\Mapper($this->ci);
        $this->budget_mapper = new Budget\Mapper($this->ci);
        $this->paymethod_mapper = new Paymethod\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {

        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');

        $data = $request->getQueryParams();
        list($from, $to) = $this->ci->get('helper')->getDateRange($data, $defaultFrom); //$range["min"], $max);
        
        $table_count = FigRequestCookies::get($request, 'perPage_financeTable', 10);
        
        $table_count_val = intval($table_count->getValue());

        $list = $this->mapper->getTableData($from, $to, 0, 'DESC', $table_count_val);
        $table = $this->renderTableRows($list);
        $datacount = $this->mapper->tableCount($from, $to);

        $range = $this->mapper->getMinMaxDate();
        $max = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        $recordSum = round($this->mapper->tableSum($from, $to, 0) - $this->mapper->tableSum($from, $to, 1), 2);

        return $this->ci->view->render($response, 'finances/index.twig', [
                    "list" => $table,
                    "datacount" => $datacount,
                    "from" => $from,
                    "to" => $to,
                    "min" => $range["min"],
                    "max" => $max,
                    "sum" => $recordSum
        ]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');
        $paymethods = $this->paymethod_mapper->getAll('name');

        return $this->ci->view->render($response, 'finances/edit.twig', ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods]);
    }

    protected function afterSave($id, array $data, Request $request) {
        $user_id = $this->ci->get('helper')->getUser()->id;

        $entry = $this->mapper->get($id);
        $cat = $this->getDefaultOrAssignedCategory($user_id, $entry);
        if (!is_null($cat)) {
            $this->mapper->set_category($id, $cat);
        }

        return $this->checkBudget($id);
    }

    public function getDefaultOrAssignedCategory($user_id, FinancesEntry $entry) {
        $default_cat = $this->cat_mapper->getDefaultofUser($user_id);
        $category = $entry->category;

        // when there is no category set the default category
        if (is_null($category) && !is_null($default_cat)) {
            $category = $default_cat;
        }

        // when it is default category then check if there is a auto assignment possible
        if ($category == $default_cat) {
            $cat = $this->cat_assignments_mapper->findMatchingCategory($user_id, $entry->description, $entry->value);
            if (!is_null($cat)) {
                $category = $cat;
            }
        }
        return $category;
    }

    private function checkBudget($id) {
        $entry = $this->mapper->get($id);
        $results = array();

        $date = new \DateTime($entry->date);
        $now = new \DateTime('now');

        if (($date->format('m') == $now->format('m')) && $entry->type == 0) {

            $budgets = $this->budget_mapper->getBudgetsFromCategory($entry->category);
            $all_budgets = $this->budget_mapper->getBudgets();

            // remains
            if (empty($budgets)) {
                $remains = $this->budget_mapper->getRemainsBudget();
                if ($remains) {
                    $remains->sum = $this->budget_mapper->getRemainsExpenses();
                    $remains->diff = $remains->value - $remains->sum;
                    $remains->percent = round((($remains->sum / $remains->value) * 100), 2);

                    $type = 'success';
                    if ($remains->percent > 80) {
                        $type = 'danger';
                    } elseif ($remains->percent > 50) {
                        $type = 'warning';
                    }

                    //$message = $this->ci->get('helper')->getTranslatedString("REMAINING_BUDGET") . " (" . $remains->description . "): " . $remains->diff . " " . $this->ci->get('settings')['app']['i18n']['currency'];
                    $message = $this->ci->get('helper')->getTranslatedString("BUDGET") . " (" . $remains->description . "): " . $remains->percent . "%";

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
                    //$message = $this->ci->get('helper')->getTranslatedString("REMAINING_BUDGET") . " (" . html_entity_decode($all_budgets[$budget->id]->description) . "): " . $all_budgets[$budget->id]->diff . " " . $this->ci->get('settings')['app']['i18n']['currency'];
                    $message = $this->ci->get('helper')->getTranslatedString("BUDGET") . " (" . html_entity_decode($all_budgets[$budget->id]->description) . "): " . $all_budgets[$budget->id]->percent . "%";

                    array_push($results, array('message' => $message, 'type' => $type));
                }
            }

            foreach ($results as $result) {
                $this->ci->get('flash')->addMessage('budget_message_type', $result["type"]);
                $this->ci->get('flash')->addMessage('budget_message', $result["message"]);
            }
        }
        return $results;
    }

    public function record(Request $request, Response $response) {

        $data = $request->getParsedBody();

        $data['user'] = $this->ci->get('helper')->getUser()->id;

        $data = array_map(function($el) {
            return urldecode($el);
        }, $data);

        if (array_key_exists('value', $data)) {
            $data['value'] = str_replace(',', '.', $data["value"]);
        }

        $entry = new FinancesEntry($data);


        if (!$entry->hasParsingErrors()) {
            try {
                $this->preSave(null, $data, $request);
                $id = $this->mapper->insert($entry);
                $budgets = $this->afterSave($id, $data, $request);

                $this->logger->addInfo("Record Finances", array("id" => $id));
            } catch (\Exception $e) {

                $this->logger->addError("Record Finances", array("error" => $e->getMessage()));

                return $response->withJSON(array('status' => 'error', 'data' => $e->getMessage()));
            }

            $message = $entry->date . ' '
                    . '(' . $entry->time . '): '
                    . '' . $entry->description . ' ' . $entry->value . ' - ' . $this->ci->get('helper')->getTranslatedString('ENTRY_SUCCESS');

            if (!empty($budgets)) {
                foreach ($budgets as $budget) {
                    $message .= ' | ' . $budget["message"];
                }
            }

            return $response->withJSON(array('status' => 'success', 'data' => $message));
        }
        return $response->withJSON(array('status' => 'error', 'data' => 'error'));
    }

    public function stats(Request $request, Response $response) {
        $stats = $this->mapper->statsTotal();

        list($data, $spendings, $income, $labels, $diff) = $this->createChartData($stats);


        return $this->ci->view->render($response, 'finances/stats/index.twig', ['stats' => $data, "data1" => $spendings, "data2" => $income, "labels" => $labels]);
    }

    public function statsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $stats = $this->mapper->statsYear($year);

        list($data, $spendings, $income, $labels, $diff) = $this->createChartData($stats, "month");

        return $this->ci->view->render($response, 'finances/stats/year.twig', ['stats' => $data, "year" => $year, "data1" => $spendings, "data2" => $income, "labels" => $labels]);
    }

    public function statsCategory(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $type = $request->getAttribute('type');

        $stats = $this->mapper->statsCategory($year, $type);
        list($labels, $data) = $this->preparePieChart($stats);

        return $this->ci->view->render($response, 'finances/stats/year_cat.twig', [
                    "stats" => $stats,
                    "type" => $type,
                    "year" => $year,
                    "data" => $data,
                    "labels" => $labels]);
    }

    public function statsMonthType(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $type = $request->getAttribute('type');

        $stats = $this->mapper->statsMonthType($year, $month, $type);
        list($labels, $data) = $this->preparePieChart($stats);


        return $this->ci->view->render($response, 'finances/stats/month.twig', [
                    "stats" => $stats,
                    "month" => $month,
                    "year" => $year,
                    "type" => $type,
                    "data" => $data,
                    "labels" => $labels]
        );
    }

    public function statsMonthCategory(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');


        $stats = $this->mapper->statsMonthCategory($year, $month, $type, $category);

        $category_name = $this->cat_mapper->get($category);

        list($labels, $data, $count) = $this->preparePieChartGrouped($stats);


        return $this->ci->view->render($response, 'finances/stats/month_cat.twig', [
                    "stats" => $stats,
                    "month" => $month,
                    "year" => $year,
                    "type" => $type,
                    "category" => $category_name->name,
                    "data" => $data,
                    "labels" => $labels]
        );
    }

    public function statsCategoryDetail(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');

        $stats = $this->mapper->statsCategoryDetail($year, $type, $category);

        $category_name = $this->cat_mapper->get($category);

        list($labels, $data, $count) = $this->preparePieChartGrouped($stats);

        return $this->ci->view->render($response, 'finances/stats/year_cat_detail.twig', [
                    "stats" => $stats,
                    "year" => $year,
                    "type" => $type,
                    "category" => $category_name->name,
                    "data" => $data,
                    "labels" => $labels,
                    "count" => $count]
        );
    }

    public function table(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        list($from, $to) = $this->ci->get('helper')->getDateRange($requestData);

        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? filter_var($requestData["searchQuery"], FILTER_SANITIZE_STRING) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? filter_var($requestData["sortDirection"], FILTER_SANITIZE_STRING) : null;

        $recordsTotal = $this->mapper->tableCount($from, $to);
        $recordsFiltered = $this->mapper->tableCount($from, $to, $searchQuery);

        // subtract expenses from income
        $recordSum = round($this->mapper->tableSum($from, $to, 0, $searchQuery) - $this->mapper->tableSum($from, $to, 1, $searchQuery), 2);

        $data = $this->mapper->getTableData($from, $to, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderTableRows($data);

        return $response->withJson([
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "sum" => $recordSum,
                    "data" => $table
        ]);
    }

    private function createChartData($stats, $key = "year") {
        $data = [];

        foreach ($stats as $el) {

            if (!array_key_exists($el[$key], $data)) {
                $data[$el[$key]] = [];
            }

            $data[$el[$key]][$el["type"]] = $el["sum"];
        }


        $spendings_data = array_map(function($el) {
            return array_key_exists(0, $el) ? $el[0]: null;
        }, $data);

        $income_data = array_map(function($el) {
            return array_key_exists(1, $el) ? $el[1]: null;
        }, $data);

        $diff_data = array_map(function($el) {
            return array_key_exists(1, $el) && array_key_exists(0, $el) ? $el[1] - $el[0]: null ;
        }, $data);

        $labels = array_keys($data);

        if ($key === "month") {
            $labels = array_map(function($l) {
                return $this->ci->get('helper')->getMonthName($l);
            }, $labels);
        }

        $spendings = json_encode(array_values($spendings_data), JSON_NUMERIC_CHECK);
        $income = json_encode(array_values($income_data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        $diff = json_encode(array_values($diff_data), JSON_NUMERIC_CHECK);

        return array($data, $spendings, $income, $labels, $diff);
    }

    private function preparePieChart($stats) {
        $labels = array_map(function($e) {
            $cat = htmlspecialchars_decode($e["category"]);
            return $cat;
        }, $stats);
        $data = array_map(function($e) {
            return $e["sum"];
        }, $stats);

        $labels = json_encode(array_values($labels), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);
        return array($labels, $data);
    }

    private function preparePieChartGrouped($stats) {
        $data = [];

        foreach ($stats as $el) {
            // filter special characters
            // group by description
            $cat = htmlspecialchars_decode($el["description"]);

            if (!array_key_exists($cat, $data)) {
                $data[$cat] = 0;
            }
            $data[$cat] += floatval($el["value"]);
        }

        $count = count($data);
        $labels = json_encode(array_keys($data), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);

        return array($labels, $data, $count);
    }

    public function statsBudget(Request $request, Response $response) {
        $budget = $request->getAttribute('budget');

        $budget_name = $this->budget_mapper->get($budget);

        $is_remains = $this->budget_mapper->isRemainsBudget($budget);

        if ($is_remains) {
            $stats = $this->mapper->statsBudgetRemains();
        } else {
            $stats = $this->mapper->statsBudget($budget);
        }

        $categories = $this->budget_mapper->getCategoriesFromBudget($budget);

        $data = [];

        foreach ($stats as $el) {
            // filter special characters
            // group by category/description
            if (count($categories) >= self::$GROUP_CATEGORIES_BUDGET_CHART || $is_remains) {
                $key = htmlspecialchars_decode($el["category"]);
            } else {
                $key = htmlspecialchars_decode($el["description"]);
            }

            if (!array_key_exists($key, $data)) {
                $data[$key] = 0;
            }
            $data[$key] += $el["value"];
        }


        $labels = json_encode(array_keys($data), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);


        return $this->ci->view->render($response, 'finances/stats/budget.twig', [
                    "stats" => $stats,
                    "budget" => $budget_name->description,
                    "data" => $data,
                    "labels" => $labels]
        );
    }

    private function renderTableRows(array $table) {
        $rendered_data = [];
        foreach ($table as $dataset) {
            $row = [];
            $row[] = $dataset[0];
            $row[] = $dataset[1];
            $row[] = $dataset[2] == 0 ? $this->ci->get('helper')->getTranslatedString("FINANCES_SPENDING") : $this->ci->get('helper')->getTranslatedString("FINANCES_INCOME");
            $row[] = $dataset[3];
            $row[] = $dataset[4];
            $row[] = $dataset[5];
            $row[] = '<a href="' . $this->ci->get('router')->pathFor('finances_edit', ['id' => $dataset[6]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[] = is_null($dataset[7]) ? '<a href="#" data-url="' . $this->ci->get('router')->pathFor('finances_delete', ['id' => $dataset[6]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>' : '';

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

    /**
     * Do not allow deletion of entries with bills
     */
    protected function preDelete($id, Request $request) {
        $entry = $this->mapper->get($id);
        if (!is_null($entry->bill)) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_ACCESS'), 404);
        }
    }

}
