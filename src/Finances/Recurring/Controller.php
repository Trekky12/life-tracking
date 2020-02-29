<?php

namespace App\Finances\Recurring;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Psr\Container\ContainerInterface;

class Controller extends \App\Base\Controller {

    protected $model = '\App\Finances\Recurring\FinancesEntryRecurring';
    protected $index_route = 'finances_recurring';
    protected $element_view_route = 'finances_recurring_edit';
    protected $module = "finances";
    private $cat_mapper;
    private $finance_mapper;
    private $paymethod_mapper;

    public function __construct(ContainerInterface $ci) {
        parent::__construct($ci);
        
        $user = $this->user_helper->getUser();
        
        $this->mapper = new Mapper($this->db, $this->translation, $user);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->db, $this->translation, $user);
        $this->finance_mapper = new \App\Finances\Mapper($this->db, $this->translation, $user);
        $this->paymethod_mapper = new \App\Finances\Paymethod\Mapper($this->db, $this->translation, $user);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $categories = $this->cat_mapper->getAll();
        return $this->twig->render($response, 'finances/recurring/index.twig', ['list' => $list, 'categories' => $categories, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');
        $paymethods = $this->paymethod_mapper->getAll('name');

        return $this->twig->render($response, 'finances/recurring/edit.twig', ['entry' => $entry, 'categories' => $categories, 'paymethods' => $paymethods, 'units' => FinancesEntryRecurring::getUnits()]);
    }

    public function update() {

        $mentries = $this->mapper->getRecurringEntries();

        if ($mentries) {
            $this->logger->addDebug('Recurring Entries', $mentries);

            foreach ($mentries as $mentry) {
                $entry = new \App\Finances\FinancesEntry([
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
                $this->finance_mapper->insert($entry);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        return true;
    }

    public function sendSummary() {

        $users = $this->user_mapper->getAll();

        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

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
                $balance["income"] = $this->finance_mapper->statsMailBalance($user->id, $month, $year, 1);
                $balance["spendings"] = $this->finance_mapper->statsMailBalance($user->id, $month, $year, 0);
                $balance["difference"] = $balance["income"] - $balance["spendings"];

                $expenses = $this->finance_mapper->statsMailExpenses($user->id, $month, $year, 10);

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
                        'currency' => $this->settings['app']['i18n']['currency'],
                        'expenses' => $expenses
                    );

                    $this->helper->send_mail('mail/stats.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

    protected function afterSave($id, array $data, Request $request) {
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

}
