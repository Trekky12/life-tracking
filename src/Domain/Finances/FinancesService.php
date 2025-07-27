<?php

namespace App\Domain\Finances;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Assignment\AssignmentService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Domain\Splitbill\Bill\SplitbillBillService;
use App\Domain\Main\Utility\Utility;

class FinancesService extends Service {

    private $translation;
    private $router;
    protected $mapper;
    private $cat_service;
    private $cat_assignments_service;
    private $paymethod_service;
    private $splitbill_bill_service;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        Translator $translation,
        RouteParser $router,
        FinancesMapper $finance_mapper,
        CategoryService $cat_service,
        AssignmentService $cat_assignments_service,
        PaymethodService $paymethod_service,
        SplitbillBillService $splitbill_bill_service
    ) {
        parent::__construct($logger, $user);

        $this->translation = $translation;
        $this->router = $router;
        $this->mapper = $finance_mapper;
        $this->cat_service = $cat_service;
        $this->cat_assignments_service = $cat_assignments_service;
        $this->paymethod_service = $paymethod_service;
        $this->splitbill_bill_service = $splitbill_bill_service;
    }

    public function financeTableIndex($from, $to, $count = 20) {

        $list = $this->getMapper()->getTableData($from, $to, 0, 'DESC', $count);
        $table = $this->renderTableRows($list);
        $datacount = $this->getMapper()->tableCount($from, $to);

        $range = $this->getMapper()->getMinMaxDate();
        $minTotal = $range["min"];
        $maxTotal = $range["max"] > date('Y-m-d') ? $range["max"] : date('Y-m-d');

        // Month Filter
        $d1 = new \DateTime('first day of this month');
        $minMonth = $d1->format('Y-m-d');
        $d2 = new \DateTime('last day of this month');
        $maxMonth = $d2->format('Y-m-d');

        $recordSum = round($this->getMapper()->tableSum($from, $to, 0) - $this->getMapper()->tableSum($from, $to, 1), 2);

        return new Payload(Payload::$RESULT_HTML, [
            "list" => $table,
            "datacount" => $datacount,
            "from" => $from,
            "to" => $to,
            "sum" => $recordSum,
            "min" => [
                "total" => $minTotal,
                "month" => $minMonth
            ],
            "max" => [
                "total" => $maxTotal,
                "month" => $maxMonth
            ],
        ]);
    }

    public function table($from, $to, $requestData): Payload {
        $start = array_key_exists("start", $requestData) ? filter_var($requestData["start"], FILTER_SANITIZE_NUMBER_INT) : null;
        $length = array_key_exists("length", $requestData) ? filter_var($requestData["length"], FILTER_SANITIZE_NUMBER_INT) : null;

        $search = array_key_exists("searchQuery", $requestData) ? Utility::filter_string_polyfill($requestData["searchQuery"]) : null;
        $searchQuery = empty($search) || $search === "null" ? "%" : "%" . $search . "%";

        $sort = array_key_exists("sortColumn", $requestData) ? filter_var($requestData["sortColumn"], FILTER_SANITIZE_NUMBER_INT) : null;
        $sortColumn = empty($sort) || $sort === "null" ? null : $sort;

        $sortDirection = array_key_exists("sortDirection", $requestData) ? Utility::filter_string_polyfill($requestData["sortDirection"]) : null;

        $recordsTotal = $this->getMapper()->tableCount($from, $to);
        $recordsFiltered = $this->getMapper()->tableCount($from, $to, $searchQuery);

        // subtract expenses from income
        $recordSum = round($this->getMapper()->tableSum($from, $to, 0, $searchQuery) - $this->getMapper()->tableSum($from, $to, 1, $searchQuery), 2);

        $data = $this->getMapper()->getTableData($from, $to, $sortColumn, $sortDirection, $length, $start, $searchQuery);
        $table = $this->renderTableRows($data);

        $response_data = [
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "sum" => $recordSum,
            "data" => $table
        ];

        return new Payload(Payload::$RESULT_JSON, $response_data);
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
            $row[] = '<a href="' . $this->router->urlFor('finances_edit', ['id' => $dataset[6]]) . '">' . Utility::getFontAwesomeIcon('fas fa-pen-to-square') . '</a>';
            $row[] = is_null($dataset[7]) ? '<a href="#" data-url="' . $this->router->urlFor('finances_delete', ['id' => $dataset[6]]) . '" class="btn-delete">' . Utility::getFontAwesomeIcon('fas fa-trash') . '</span></a>' : '';

            $rendered_data[] = $row;
        }
        return $rendered_data;
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        $paymethods = $this->paymethod_service->getAllPaymethodsOrderedByName();

        return new Payload(Payload::$RESULT_HTML, [
            'entry' => $entry,
            'categories' => $categories,
            'paymethods' => $paymethods
        ]);
    }

    public function isSplittedBillEntry($id) {
        $entry = $this->getMapper()->get($id);
        if (!is_null($entry->bill)) {
            return true;
        }
        return false;
    }

    public function getDefaultOrAssignedCategory(FinancesEntry $entry) {
        $default_cat = $this->cat_service->getDefaultCategoryOfUser($entry->user);
        $category = $entry->category;

        // when there is no category set the default category
        if (is_null($category) && !is_null($default_cat)) {
            $category = $default_cat;
        }

        // when it is default category then check if there is a auto assignment possible
        if ($category == $default_cat) {
            $cat = $this->cat_assignments_service->findMatchingCategory($entry);
            if (!is_null($cat)) {
                $category = $cat;
            }
        }
        return $category;
    }

    public function deleteEntrywithBill($bill, $user) {
        return $this->getMapper()->deleteEntrywithBill($bill, $user);
    }

    public function getMarkers($from, $to) {
        return $this->getMapper()->getMarkers($from, $to);
    }
}
