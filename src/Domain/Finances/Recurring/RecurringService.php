<?php

namespace App\Domain\Finances\Recurring;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Payload\Payload;

class RecurringService extends Service {

    private $paymethod_service;
    private $cat_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, RecurringMapper $mapper, CategoryService $cat_service, PaymethodService $paymethod_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->cat_service = $cat_service;
        $this->paymethod_service = $paymethod_service;
    }

    public function getAllRecurring() {
        return $this->mapper->getAll();
    }

    private function getSumOfCategories(array $categories) {
        return $this->mapper->getSumOfCategories($categories);
    }

    public function index() {
        $list = $this->getAllRecurring();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return new Payload(Payload::$RESULT_HTML, ['list' => $list, 'categories' => $categories, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        $paymethods = $this->paymethod_service->getAllPaymethodsOrderedByName();

        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function getCategoryCosts($category) {
        if (is_null($category)) {
            $response_data = ['status' => 'error', "error" => "empty"];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }

        try {
            $categories = filter_var_array($category, FILTER_SANITIZE_NUMBER_INT);
            $sum = $this->getSumOfCategories($categories);
        } catch (\Exception $e) {
            $this->logger->addError("Get Category Costs", array("data" => $category, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return new Payload(Payload::$RESULT_JSON, $response_data);
        }

        $response_data = ['status' => 'success', 'value' => $sum];
        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
