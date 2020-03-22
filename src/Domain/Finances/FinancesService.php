<?php

namespace App\Domain\Finances;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Assignment\AssignmentService;

class FinancesService extends \App\Domain\Service {

    protected $module = "finances";
    protected $dataobject = \App\Domain\Finances\FinancesEntry::class;
    private $cat_service;
    private $cat_assignments_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $finance_mapper,
            CategoryService $cat_service,
            AssignmentService $cat_assignments_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $finance_mapper;
        $this->cat_service = $cat_service;
        $this->cat_assignments_service = $cat_assignments_service;
    }

    public function financeTableIndex($from, $to, $count = 10) {

        $list = $this->mapper->getTableData($from, $to, 0, 'DESC', $count);
        $table = $this->renderTableRows($list);
        $datacount = $this->mapper->tableCount($from, $to);

        $range = $this->mapper->getMinMaxDate();
        $max = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        $recordSum = round($this->mapper->tableSum($from, $to, 0) - $this->mapper->tableSum($from, $to, 1), 2);

        return [
            "list" => $table,
            "datacount" => $datacount,
            "from" => $from,
            "to" => $to,
            "min" => $range["min"],
            "max" => $max,
            "sum" => $recordSum
        ];
    }

    public function table($from, $to, $requestData) {
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

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "sum" => $recordSum,
            "data" => $table
        ];

        return $response_data;
    }

    private function renderTableRows(array $table) {
        $rendered_data = [];
        foreach ($table as $dataset) {
            $row = [];
            $row[] = $dataset[0];
            $row[] = $dataset[1];
            $row[] = $dataset[2] == 0 ? $this->translation->getTranslatedString("FINANCES_SPENDING") : $this->translation->getTranslatedString("FINANCES_INCOME");
            $row[] = $dataset[3];
            $row[] = $dataset[4];
            $row[] = $dataset[5];
            $row[] = '<a href="' . $this->router->urlFor('finances_edit', ['id' => $dataset[6]]) . '"><span class="fas fa-edit fa-lg"></span></a>';
            $row[] = is_null($dataset[7]) ? '<a href="#" data-url="' . $this->router->urlFor('finances_delete', ['id' => $dataset[6]]) . '" class="btn-delete"><span class="fas fa-trash fa-lg"></span></a>' : '';

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

    public function setDefaultOrAssignedCategory($id) {
        $user_id = $this->current_user->getUser()->id;

        $entry = $this->mapper->get($id);
        $cat = $this->getDefaultOrAssignedCategory($user_id, $entry);
        if (!is_null($cat)) {
            $this->mapper->set_category($id, $cat);
        }
    }

    public function getDefaultOrAssignedCategory($user_id, FinancesEntry $entry) {
        $default_cat = $this->cat_service->getDefaultCategoryOfUser($user_id);
        $category = $entry->category;

        // when there is no category set the default category
        if (is_null($category) && !is_null($default_cat)) {
            $category = $default_cat;
        }

        // when it is default category then check if there is a auto assignment possible
        if ($category == $default_cat) {
            $cat = $this->cat_assignments_service->findMatchingCategory($user_id, $entry);
            if (!is_null($cat)) {
                $category = $cat;
            }
        }
        return $category;
    }

    public function isSplittedBillEntry($id) {
        $entry = $this->mapper->get($id);
        if (!is_null($entry->bill)) {
            return true;
        }
        return false;
    }

    public function addOrUpdateFromBill(FinancesEntry $entry) {
        return $this->mapper->addOrUpdateFromBill($entry);
    }

    public function deleteEntrywithBill($bill, $user) {
        return $this->mapper->deleteEntrywithBill($bill, $user);
    }

    public function getMarkers($from, $to) {
        return $this->mapper->getMarkers($from, $to);
    }

}
