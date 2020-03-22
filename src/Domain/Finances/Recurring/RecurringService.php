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

class RecurringService extends \App\Domain\Service {

    protected $dataobject = \App\Domain\Finances\Recurring\FinancesEntryRecurring::class;
    protected $element_view_route = 'finances_recurring_edit';
    protected $module = "finances";
    private $finance_service;
    private $finances_stats_service;
    private $user_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            FinancesService $finances_service,
            FinancesStatsService $finances_stats_service,
            Helper $helper,
            UserService $user_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
        $this->finance_service = $finances_service;
        $this->finances_stats_service = $finances_stats_service;
        $this->helper = $helper;
        $this->user_service = $user_service;
    }

    public function getAllRecurring() {
        return $this->mapper->getAll();
    }

    public function getSumOfAllCategories() {
        return $this->mapper->getSumOfAllCategories();
    }

    public function getSumOfCategories(array $categories) {
        return $this->mapper->getSumOfCategories($categories);
    }

    public function getSumIncome() {
        return $this->mapper->getSum(1);
    }

    public function setLastRun($id) {
        $entry = $this->mapper->get($id);

        /**
         * When the entry is new but has an past start date set the last run to this date
         */
        if (is_null($entry->last_run) && !is_null($entry->start)) {
            $start = new \DateTime($entry->start);
            $now = new \DateTime('now');

            $start->setTime(0, 0, 0);
            $now->setTime(0, 0, 0);

            if ($now > $start) {
                $this->mapper->setLastRun($id, $start->format("Y-m-d"));
            }
        }
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

}
