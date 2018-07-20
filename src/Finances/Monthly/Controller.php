<?php

namespace App\Finances\Monthly;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Controller extends \App\Base\Controller {

    private $cat_mapper;
    private $finance_mapper;

    public function init() {
        $this->model = '\App\Finances\Monthly\FinancesEntryMonthly';
        $this->index_route = 'finances_monthly';

        $this->mapper = new \App\Finances\Monthly\Mapper($this->ci);
        $this->cat_mapper = new \App\Finances\Category\Mapper($this->ci);
        $this->finance_mapper = new \App\Finances\Mapper($this->ci);
    }

    public function index(Request $request, Response $response) {
        $list = $this->mapper->getAll();
        $categories = $this->cat_mapper->getAll();
        return $this->ci->view->render($response, 'finances/monthly/index.twig', ['list' => $list, 'categories' => $categories]);
    }

    public function edit(Request $request, Response $response) {

        $entry_id = $request->getAttribute('id');

        $entry = null;
        if (!empty($entry_id)) {
            $entry = $this->mapper->get($entry_id);
        }

        $categories = $this->cat_mapper->getAll('name');

        return $this->ci->view->render($response, 'finances/monthly/edit.twig', ['entry' => $entry, 'categories' => $categories]);
    }

    public function update(Request $request, Response $response) {

        $mentries = $this->mapper->getMonthlyEntries();

        if ($mentries) {
            foreach ($mentries as $mentry) {
                $entry = new \App\Finances\FinancesEntry([
                    'type' => $mentry->type,
                    'category' => $mentry->category,
                    'description' => $mentry->description,
                    'value' => $mentry->value,
                    'common' => $mentry->common,
                    'common_value' => $mentry->common_value,
                    'notice' => $mentry->notice,
                    'user' => $mentry->user
                ]);
                $this->finance_mapper->insert($entry);
            }

            $mentry_ids = array_map(function($el) {
                return $el->id;
            }, $mentries);
            $this->mapper->updateLastRun($mentry_ids);
        }

        // Is first of month?
        $date = new \DateTime('now');
        if ($date->format("d") === "01") {
            $this->sendSummary();
        }

        return $response->withJSON(array('result' => 'success'));
    }

    private function sendSummary() {

        $users = $this->user_mapper->getAll();

        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt->setPattern('MMMM');
        $dateObj = new \DateTime('first day of last month');
        $month = $dateObj->format("m");
        $year = $dateObj->format("Y");

        $subject = sprintf('[Life-Tracking] %s %s %s %s', $this->ci->get('helper')->getTranslatedString('STATS'), $this->ci->get('helper')->getTranslatedString('FOR'), $fmt->format($dateObj), $year);

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
                        'headline' => sprintf($this->ci->get('helper')->getTranslatedString('HELLO') . ' %s', $user->name),
                        'content' => sprintf($this->ci->get('helper')->getTranslatedString('YOUR_MONTHLY_STATISTIC'), $fmt->format($dateObj)),
                        'LANG_YOUR_BALANCE' => $this->ci->get('helper')->getTranslatedString('YOUR_BALANCE'),
                        'LANG_YOUR_BIGGEST_EXPENSES' => $this->ci->get('helper')->getTranslatedString('YOUR_BIGGEST_EXPENSES'),
                        'LANG_INCOMES' => $this->ci->get('helper')->getTranslatedString('FINANCES_INCOMES'),
                        'LANG_SPENDINGS' => $this->ci->get('helper')->getTranslatedString('FINANCES_SPENDINGS'),
                        'LANG_DIFFERENCE' => $this->ci->get('helper')->getTranslatedString('DIFFERENCE'),
                        'balance' => $balance,
                        'currency' => $this->ci->get('settings')['app']['i18n']['currency'],
                        'expenses' => $expenses
                    );

                    $this->ci->get('helper')->send_mail('mail/stats.twig', $user->mail, $subject, $variables);
                }
            }
        }
    }

}
