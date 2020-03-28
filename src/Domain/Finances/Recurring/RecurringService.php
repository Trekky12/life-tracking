<?php

namespace App\Domain\Finances\Recurring;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Helper;
use App\Domain\User\UserService;
use App\Domain\Finances\FinancesEntry;
use App\Domain\Finances\FinancesService;
use App\Domain\Finances\FinancesStatsService;
use App\Domain\Finances\Category\CategoryService;
use App\Domain\Finances\Paymethod\PaymethodService;
use App\Application\Payload\Payload;

class RecurringService extends \App\Domain\Service {

    private $finance_service;
    private $finances_stats_service;
    private $paymethod_service;
    private $cat_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            RecurringMapper $mapper,
            FinancesService $finances_service,
            FinancesStatsService $finances_stats_service,
            CategoryService $cat_service,
            PaymethodService $paymethod_service,
            Helper $helper,
            UserService $user_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->finance_service = $finances_service;
        $this->finances_stats_service = $finances_stats_service;
        $this->cat_service = $cat_service;
        $this->paymethod_service = $paymethod_service;
        $this->helper = $helper;
        $this->user_service = $user_service;
    }

    public function getAllRecurring() {
        return $this->mapper->getAll();
    }

    private function getSumOfCategories(array $categories) {
        return $this->mapper->getSumOfCategories($categories);
    }

    /**
     * Cron
     */
    public function update() {

        $mentries = $this->mapper->getRecurringEntries();

        if ($mentries) {
            $this->logger->addDebug('Recurring Entries', $mentries);

            foreach ($mentries as $mentry) {
                $entry = new FinancesEntry([
                    'type' => $mentry->type,
                    'category' => $mentry->category,
                    'description' => $mentry->description,
                    'value' => $mentry->value,
                    'common' => $mentry->common,
                    'common_value' => $mentry->common_value,
                    'notice' => $mentry->notice,
                    'user' => $mentry->user,
                    'fixed' => 1,
                    'paymethod' => $mentry->paymethod
                ]);
                $this->finance_service->insertEntry($entry);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

    public function sendSummary() {

        $users = $this->user_service->getAll();

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP["month_name"]);
        $dateObj = new \DateTime('first day of last month');
        $month = $dateObj->format("m");
        $year = $dateObj->format("Y");

        $subject = sprintf('[Life-Tracking] %s %s %s %s', $this->translation->getTranslatedString('STATS'), $this->translation->getTranslatedString('FOR'), $fmt->format($dateObj), $year);

        foreach ($users as $user) {
            if ($user->mail && $user->mails_finances == 1) {

                /**
                 * Calculate Statistic
                 */
                $balance = [];
                $balance["income"] = $this->finances_stats_service->statsMailBalance($user->id, $month, $year, 1);
                $balance["spendings"] = $this->finances_stats_service->statsMailBalance($user->id, $month, $year, 0);
                $balance["difference"] = $balance["income"] - $balance["spendings"];

                $expenses = $this->finances_stats_service->statsMailExpenses($user->id, $month, $year, 10);

                if ($balance["income"] > 0 || $balance["spendings"] > 0) {

                    /**
                     * Send mail
                     */
                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => sprintf($this->translation->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->translation->getTranslatedString('YOUR_MONTHLY_STATISTIC'), $fmt->format($dateObj)),
                        'LANG_YOUR_BALANCE' => $this->translation->getTranslatedString('YOUR_BALANCE'),
                        'LANG_YOUR_BIGGEST_EXPENSES' => $this->translation->getTranslatedString('YOUR_BIGGEST_EXPENSES'),
                        'LANG_INCOMES' => $this->translation->getTranslatedString('FINANCES_INCOMES'),
                        'LANG_SPENDINGS' => $this->translation->getTranslatedString('FINANCES_SPENDINGS'),
                        'LANG_DIFFERENCE' => $this->translation->getTranslatedString('DIFFERENCE'),
                        'balance' => $balance,
                        'currency' => $this->settings->getAppSettings()['i18n']['currency'],
                        'expenses' => $expenses
                    );

                    $this->helper->send_mail('mail/stats.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

    public function index() {
        $list = $this->getAllRecurring();
        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        return ['list' => $list, 'categories' => $categories, 'units' => FinancesEntryRecurring::getUnits()];
    }

    public function edit($entry_id) {
        $entry = $this->getEntry($entry_id);

        $categories = $this->cat_service->getAllCategoriesOrderedByName();
        $paymethods = $this->paymethod_service->getAllPaymethodsOrderedByName();

        return ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods, 'units' => FinancesEntryRecurring::getUnits()];
    }

    public function getCategoryCosts($category) {
        if (is_null($category)) {
            $response_data = ['status' => 'error', "error" => "empty"];
            return new Payload(null, $response_data);
        }

        try {
            $categories = filter_var_array($category, FILTER_SANITIZE_NUMBER_INT);
            $sum = $this->getSumOfCategories($categories);
        } catch (\Exception $e) {
            $this->logger->addError("Get Category Costs", array("data" => $category, "error" => $e->getMessage()));

            $response_data = ['status' => 'error', "error" => $e->getMessage()];
            return new Payload(null, $response_data);
        }

        $response_data = ['status' => 'success', 'value' => $sum];
        return new Payload(null, $response_data);
    }

}
