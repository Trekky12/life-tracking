<?php

namespace App\Domain\Finances;

use Psr\Log\LoggerInterface;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Main\Helper;
use App\Domain\User\UserService;
use App\Domain\Finances\FinancesMapper;
use App\Domain\Notifications\NotificationsService;

class FinanceStatsMonthlyMailService {

    private $logger;
    private $settings;
    private $translation;
    private $finances_mapper;
    private $helper;
    private $user_service;
    private $notification_service;

    public function __construct(
        LoggerInterface $logger,
        Settings $settings,
        Translator $translation,
        FinancesMapper $finances_mapper,
        Helper $helper,
        UserService $user_service,
        NotificationsService $notification_service
    ) {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->translation = $translation;
        $this->finances_mapper = $finances_mapper;
        $this->helper = $helper;
        $this->user_service = $user_service;
        $this->notification_service = $notification_service;
    }

    public function sendSummary() {

        $users = $this->user_service->getAll();

        $language = $this->settings->getAppSettings()['i18n']['php'];
        $dateFormatPHP = $this->settings->getAppSettings()['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language);
        $fmt->setPattern($dateFormatPHP["month_name"]);
        $dateObj = new \DateTime('first day of last month');
        $month = $dateObj->format("m");
        $year = $dateObj->format("Y");

        $subject = sprintf('[Life-Tracking] %s %s %s %s', $this->translation->getTranslatedString('STATS'), $this->translation->getTranslatedString('FOR'), $fmt->format($dateObj), $year);

        foreach ($users as $user) {
            if ($user->mail) {

                /**
                 * Calculate Statistic
                 */
                $balance = [];
                $balance["income"] = $this->finances_mapper->statsMailBalance($user->id, $month, $year, 1);
                $balance["spendings"] = $this->finances_mapper->statsMailBalance($user->id, $month, $year, 0);
                $balance["difference"] = $balance["income"] - $balance["spendings"];

                $expenses = $this->finances_mapper->statsMailExpenses($user->id, $month, $year, 10);

                if ($balance["income"] > 0 || $balance["spendings"] > 0) {

                    /**
                     * Send mail
                     */
                    $variables = array(
                        'header' => '',
                        'subject' => $subject,
                        'headline' => $this->translation->getTranslatedString('HELLO_USER', ['%username%' => $user->name]),
                        'content' => $this->translation->getTranslatedString('YOUR_MONTHLY_STATISTIC', ['%month%' => $fmt->format($dateObj)]),
                        'LANG_YOUR_BALANCE' => $this->translation->getTranslatedString('YOUR_BALANCE'),
                        'LANG_YOUR_BIGGEST_EXPENSES' => $this->translation->getTranslatedString('YOUR_BIGGEST_EXPENSES'),
                        'LANG_INCOMES' => $this->translation->getTranslatedString('FINANCES_INCOMES'),
                        'LANG_SPENDINGS' => $this->translation->getTranslatedString('FINANCES_SPENDINGS'),
                        'LANG_DIFFERENCE' => $this->translation->getTranslatedString('DIFFERENCE'),
                        'balance' => $balance,
                        'currency' => $this->settings->getAppSettings()['i18n']['currency'],
                        'expenses' => $expenses
                    );

                    //$this->helper->send_mail('mail/stats.twig', $user->mail, $subject, $variables);
                    $this->notification_service->sendMailNotificationToUserWithCategory($user, "MAIL_CATEGORY_FINANCE_STATISTIC", 'mail/stats.twig', $subject, $variables);
                }
            }
        }
    }
}
