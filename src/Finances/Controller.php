<?php

namespace App\Finances;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $cat_mapper;
    private $cat_assignments_mapper;

    public function init() {
        $this->model = '\App\Finances\FinancesEntry';
        $this->index_route = 'finances';

        $this->mapper = new \App\Finances\Mapper($this->ci);
        $this->cat_mapper = new \App\FinancesCategory\Mapper($this->ci);
        $this->cat_assignments_mapper = new \App\FinancesCategoryAssignment\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll('date DESC, time DESC', 10);
        $categories = $this->cat_mapper->getAll();
        $datacount = $this->mapper->count();
        return $this->ci->view->render($response, 'finances/index.twig', ['list' => $list, 'categories' => $categories, "datacount" => $datacount]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->ci->view->render($response, 'finances/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function afterSave($id, $data) {
        $entry = $this->mapper->get($id);
        $default_cat = $this->cat_mapper->get_default();

        // when there is no category set the default category
        if (is_null($entry->category)) {
            $entry->category = $default_cat;
            $this->mapper->set_category($id, $default_cat);
        }

        // when it is default category then check if there is a auto assignment possible
        if ($entry->category == $default_cat) {
            $cat = $this->cat_assignments_mapper->get_category($entry->description, $entry->value);
            if (!is_null($cat)) {
                $this->mapper->set_category($id, $cat);
            }
        }
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
                $this->preSave(null, $data);
                $id = $this->mapper->insert($entry);
                $this->afterSave($id, $data);
            } catch (\Exception $e) {
                return $response->withJSON(array('status' => 'error', 'data' => $e->getMessage()));
            }

            return $response->withJSON(array('status' => 'success', 'data' => $entry->date . ' (' . $entry->time . '): ' . $entry->description . ' ' . $entry->value . ' - ' . $this->ci->get('helper')->getTranslatedString('ENTRY_SUCCESS')));
        }
        return $response->withJSON(array('status' => 'error', 'data' => 'error'));
    }

    public function stats(Request $request, Response $response) {
        $stats = $this->mapper->statsTotal();

        list($data, $spendings, $income, $labels) = $this->createChartData($stats);


        return $this->ci->view->render($response, 'finances/stats/index.twig', ['stats' => $data, "data1" => $spendings, "data2" => $income, "labels" => $labels]);
    }

    public function statsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $stats = $this->mapper->statsYear($year);

        list($data, $spendings, $income, $labels) = $this->createChartData($stats, "month");

        return $this->ci->view->render($response, 'finances/stats/year.twig', ['stats' => $data, "year" => $year, "data1" => $spendings, "data2" => $income, "labels" => $labels]);
    }

    public function statsMonthType(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $type = $request->getAttribute('type');

        $stats = $this->mapper->statsMonthType($year, $month, $type);

        $labels = array_map(function($e) {
            return $e["category"];
        }, $stats);
        $data = array_map(function($e) {
            return $e["sum"];
        }, $stats);

        $labels = json_encode(array_values($labels), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);

        return $this->ci->view->render($response, 'finances/stats/month.twig', [
                    'stats' => $stats,
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

        $data = [];

        foreach ($stats as $el) {
            if (!array_key_exists($el["description"], $data)) {
                $data[$el["description"]] = 0;
            }
            $data[$el["description"]] += $el["value"];
        }

        $labels = json_encode(array_keys($data), JSON_NUMERIC_CHECK);
        $data = json_encode(array_values($data), JSON_NUMERIC_CHECK);


        return $this->ci->view->render($response, 'finances/stats/cat.twig', [
                    "stats" => $stats,
                    "month" => $month,
                    "year" => $year,
                    "type" => $type,
                    "category" => $category_name->name,
                    "data" => $data,
                    "labels" => $labels]
        );
    }

    public function table(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $columns = array(
            array('db' => 'date', 'dt' => 0),
            array('db' => 'time', 'dt' => 1),
            array(
                'db' => 'type',
                'dt' => 2,
                'formatter' => function( $d, $row ) {
                    return $d == 0 ? $this->ci->get('helper')->getTranslatedString("FINANCES_SPENDING") : $this->ci->get('helper')->getTranslatedString("FINANCES_INCOME");
                }
            ),
            array('db' => 'category', 'dt' => 3,),
            array('db' => 'description', 'dt' => 4),
            array('db' => 'value', 'dt' => 5),
            array(
                'db' => 'id',
                'dt' => 6,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('finances_edit', ['id' => $d]);
                    return '<a href="' . $link . '"><span class="fa fa-pencil-square-o fa-lg"></span></a>';
                }
            ),
            array(
                'db' => 'id',
                'dt' => 7,
                'formatter' => function( $d, $row ) {
                    $link = $this->ci->get('router')->pathFor('finances_delete', ['id' => $d]);
                    return '<a href="#" data-url="' . $link . '" class="btn-delete"><span class="fa fa-trash fa-lg"></span></a>';
                }
            )
        );

        /**
         * @see https://github.com/DataTables/DataTablesSrc/blob/master/examples/server_side/scripts/ssp.class.php
         */
        $bindings = array();

        $limit = \App\Main\SSP::limit($requestData, $columns);
        $order = \App\Main\SSP::order($requestData, $columns);
        $where = \App\Main\SSP::filter($requestData, $columns, $bindings);

        $data = $this->mapper->dataTable($where, $bindings, $order, $limit);
        $recordsFiltered = $this->mapper->dataTableCount($where, $bindings);
        $recordsTotal = $this->mapper->count();

        return $response->withJson([
                    "draw" => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
                    "recordsTotal" => intval($recordsTotal),
                    "recordsFiltered" => intval($recordsFiltered),
                    "data" => \App\Main\SSP::data_output($columns, $data)
                        ]
        );
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
            return $el[0];
        }, $data);

        $income_data = array_map(function($el) {
            return $el[1];
        }, $data);

        $labels = array_keys($data);

        if ($key === "month") {
            $labels = array_map(function($l) {
                return $this->getMonthName($l);
            }, $labels);
        }

        $spendings = json_encode(array_values($spendings_data), JSON_NUMERIC_CHECK);
        $income = json_encode(array_values($income_data), JSON_NUMERIC_CHECK);
        $labels = json_encode($labels, JSON_NUMERIC_CHECK);

        return array($data, $spendings, $income, $labels);
    }

    private function getMonthName($month) {
        $langugage = $this->ci->get('settings')['app']['i18n']['php'];

        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        // See: http://userguide.icu-project.org/formatparse/datetime for pattern syntax
        $fmt->setPattern('MMMM');

        $dateObj = \DateTime::createFromFormat('!m', $month);
        return $fmt->format($dateObj);
    }

}
