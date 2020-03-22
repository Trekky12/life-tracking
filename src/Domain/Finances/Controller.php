<?php

namespace App\Domain\Finances;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Main\Utility\DateUtility;
use Dflydev\FigCookies\FigRequestCookies;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Domain\Finances\Budget\BudgetService;

class Controller extends \App\Domain\Base\Controller {

    protected $element_view_route = 'finances_edit';
    private $stats_service;
    private $cat_service;
    private $paymethod_service;
    private $budget_service;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            FinancesService $service,
            FinancesStatsService $stats_service,
            CategoryService $cat_service,
            PaymethodService $paymethod_service,
            BudgetService $budget_service) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;
        $this->service = $service;
        $this->stats_service = $stats_service;
        $this->cat_service = $cat_service;
        $this->paymethod_service = $paymethod_service;
        $this->budget_service = $budget_service;
    }

    public function index(Request $request, Response $response) {

        $requestData = $request->getQueryParams();

        $table_count = FigRequestCookies::get($request, 'perPage_financeTable', 10);
        $table_count_val = intval($table_count->getValue());

        $d = new \DateTime('first day of this month');
        $defaultFrom = $d->format('Y-m-d');

        list($from, $to) = DateUtility::getDateRange($requestData, $defaultFrom);

        $index = $this->service->financeTableIndex($from, $to, $table_count_val);

        return $this->twig->render($response, 'finances/index.twig', $index);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = $this->service->getEntry($entry_id);

        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        $paymethods = $this->paymethod_service->getAllPaymethodsOrderedByName();

        return $this->twig->render($response, 'finances/edit.twig', ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods]);
    }

    public function save(Request $request, Response $response) {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();

        $new_id = $this->doSave($id, $data, null);

        // set default or assignet category
        $this->service->setDefaultOrAssignedCategory($new_id);
        // Check Budget
        $entry = $this->service->getEntry($new_id);
        $budget_result = $this->budget_service->checkBudget($entry);
        foreach ($budget_result as $result) {
            $this->flash->addMessage('budget_message_type', $result["type"]);
            $this->flash->addMessage('budget_message', $result["message"]);
        }

        $redirect_url = $this->router->urlFor('finances');
        return $response->withRedirect($redirect_url, 301);
    }

    public function delete(Request $request, Response $response) {
        $id = $request->getAttribute('id');

        try {
            
            $is_splitted = $this->service->isSplittedBillEntry($id);
            if ($is_splitted) {
                $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('NO_ACCESS')];
            } else {
                $response_data = $this->doDelete($id);
            }
        } catch (\Exception $ex) {
            $response_data = ['is_deleted' => false, 'error' => $this->translation->getTranslatedString('ELEMENT_NOT_FOUND')];
        }

        return $response->withJson($response_data);
    }

    public function stats(Request $request, Response $response) {

        $stats = $this->stats_service->statsTotal();

        return $this->twig->render($response, 'finances/stats/index.twig', $stats);
    }

    public function statsYear(Request $request, Response $response) {
        $year = $request->getAttribute('year');

        $stats = $this->stats_service->statsYear($year);

        return $this->twig->render($response, 'finances/stats/year.twig', $stats);
    }

    public function statsMonthType(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $type = $request->getAttribute('type');

        $stats = $this->stats_service->statsYearMonthType($year, $month, $type);

        return $this->twig->render($response, 'finances/stats/month.twig', $stats);
    }

    public function statsMonthCategory(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $month = $request->getAttribute('month');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');

        $stats = $this->stats_service->statsYearMonthTypeCategory($year, $month, $type, $category);

        return $this->twig->render($response, 'finances/stats/month_cat.twig', $stats);
    }

    public function statsCategory(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $type = $request->getAttribute('type');

        $stats = $this->stats_service->statsYearType($year, $type);

        return $this->twig->render($response, 'finances/stats/year_cat.twig', $stats);
    }

    public function statsCategoryDetail(Request $request, Response $response) {
        $year = $request->getAttribute('year');
        $category = $request->getAttribute('category');
        $type = $request->getAttribute('type');

        $stats = $this->stats_service->statsYearTypeCategory($year, $type, $category);

        return $this->twig->render($response, 'finances/stats/year_cat_detail.twig', $stats);
    }

    public function table(Request $request, Response $response) {
        $requestData = $request->getQueryParams();

        list($from, $to) = DateUtility::getDateRange($requestData);
        $response_data = $this->service->table($from, $to, $requestData);

        return $response->withJson($response_data);
    }

    public function statsBudget(Request $request, Response $response) {
        $budget = $request->getAttribute('budget');

        $data = $this->stats_service->budget($budget);

        return $this->twig->render($response, 'finances/stats/budget.twig', $data);
    }

}
