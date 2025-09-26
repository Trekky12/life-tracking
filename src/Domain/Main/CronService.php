<?php

namespace App\Domain\Main;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\Finances\Recurring\RecurringFinanceEntryCreator;
use App\Domain\Finances\FinanceStatsMonthlyMailService;
use App\Domain\User\Token\TokenService;
use App\Domain\Board\Card\CardMailService;
use App\Application\Payload\Payload;
use App\Domain\Settings\SettingsMapper;
use App\Domain\Splitbill\RecurringBill\RecurringBillEntryCreator;
use App\Domain\Finances\TransactionRecurring\RecurringTransactionCreator;
use App\Domain\Main\Helper;
use App\Domain\Base\Settings;
use App\Domain\Notifications\NotificationsService;
use App\Domain\Timesheets\Project\ProjectService;
use App\Domain\Timesheets\Sheet\SheetService;
use App\Domain\User\UserService;
use Slim\Routing\RouteParser;

class CronService extends Service {

    protected $settings_mapper;
    protected $finances_entry_creator;
    protected $finance_stats_monthly_mail_service;
    protected $card_mail_service;
    protected $token_service;
    private $bill_entry_creator;
    protected $transaction_recurring_creator;
    protected $user_service;
    protected $sheet_service;
    protected $timesheet_project_service;
    protected $notifications_service;
    protected $router;
    protected $translation;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SettingsMapper $settings_mapper,
        RecurringFinanceEntryCreator $finances_entry_creator,
        FinanceStatsMonthlyMailService $finance_stats_monthly_mail_service,
        CardMailService $card_mail_service,
        TokenService $token_service,
        RecurringBillEntryCreator $bill_entry_creator,
        RecurringTransactionCreator $transaction_recurring_creator,
        Settings $settings,
        Helper $helper,
        UserService $user_service,
        SheetService $sheet_service,
        ProjectService $timesheet_project_service,
        NotificationsService $notifications_service,
        RouteParser $router,
        Translator $translation
    ) {
        parent::__construct($logger, $user);

        $this->settings_mapper = $settings_mapper;
        $this->finances_entry_creator = $finances_entry_creator;
        $this->finance_stats_monthly_mail_service = $finance_stats_monthly_mail_service;
        $this->card_mail_service = $card_mail_service;
        $this->bill_entry_creator = $bill_entry_creator;
        $this->token_service = $token_service;
        $this->transaction_recurring_creator = $transaction_recurring_creator;
        $this->user_service = $user_service;
        $this->sheet_service = $sheet_service;
        $this->timesheet_project_service = $timesheet_project_service;
        $this->notifications_service = $notifications_service;
        $this->router = $router;
        $this->translation = $translation;
    }

    public function cron(): Payload {
        $this->logger->info('Running CRON');

        $response_data = ['result' => 'unknown'];

        $isCronRunning = $this->settings_mapper->getSetting("isCronRunning");
        if (!$isCronRunning->getValue()) {

            $this->settings_mapper->updateSetting("isCronRunning", 1);

            $lastRunRecurring = $this->settings_mapper->getSetting("lastRunRecurring");
            $lastRunFinanceSummary = $this->settings_mapper->getSetting("lastRunFinanceSummary");
            $lastRunCardReminder = $this->settings_mapper->getSetting("lastRunCardReminder");
            $lastRunRecurringSplitbills = $this->settings_mapper->getSetting("lastRunRecurringSplitbills");
            $lastRunRecurringTransactions = $this->settings_mapper->getSetting("lastRunRecurringTransactions");

            $date = new \DateTime('now');

            // Update recurring finances @ 06:00
            if ($date->format("H") === "06" && $lastRunRecurring->getDayDiff() > 0) {
                $this->logger->notice('CRON - Update Finances');

                $this->finances_entry_creator->update();
                $this->settings_mapper->updateLastRun("lastRunRecurring");
            }

            // Is first of month @ 08:00? Send Finance Summary
            if ($date->format("d") === "01" && $date->format("H") === "08" && $lastRunFinanceSummary->getDayDiff() > 0) {
                $this->logger->notice('CRON - Send Finance Summary');

                $this->finance_stats_monthly_mail_service->sendSummary();
                $this->settings_mapper->updateLastRun("lastRunFinanceSummary");
            }

            // card reminder @ 09:00
            if ($date->format("H") === "09" && $lastRunCardReminder->getDayDiff() > 0) {
                $this->logger->notice('CRON - Send Card Reminder');

                $this->card_mail_service->sendReminder();
                $this->settings_mapper->updateLastRun("lastRunCardReminder");

                //            $this->token_service->deleteOldTokens();
            }

            // Update recurring splitted bills @ 06:00
            if ($date->format("H") === "06" && $lastRunRecurringSplitbills->getDayDiff() > 0) {
                $this->logger->notice('CRON - Update Splitted Bills');

                $this->bill_entry_creator->update();
                $this->settings_mapper->updateLastRun("lastRunRecurringSplitbills");
            }

            // Update recurring finance transactions @ 06:00
            if ($date->format("H") === "06" && $lastRunRecurringTransactions->getDayDiff() > 0) {
                $this->logger->notice('CRON - Update finance transactions');

                $this->transaction_recurring_creator->update();
                $this->settings_mapper->updateLastRun("lastRunRecurringTransactions");
            }

            // Check timesheet reminder beginning @ 18:00
            if (intval($date->format("H")) >= 18) {
                $projects = $this->timesheet_project_service->getAll();
                foreach ($projects as $project) {

                    $lastRunTimesheetNotifyProject = $this->settings_mapper->getSetting('lastRunTimesheetNotifyProject', $project->id);
                    if (is_null($lastRunTimesheetNotifyProject)) {
                        $this->settings_mapper->addSetting('lastRunTimesheetNotifyProject', 0, 'Date', $project->id);
                    }
                    $notifiedToday = !is_null($lastRunTimesheetNotifyProject) && $lastRunTimesheetNotifyProject->getDayDiff() == 0;

                    if (!$notifiedToday) {
                        $isLastSheetOfTheDayOverSince1hour = $this->sheet_service->isLastSheetOfTheDayOverSince1hour($project->id);
                        if ($isLastSheetOfTheDayOverSince1hour) {
                            $this->logger->debug("Send notification to users", ["project" => $project->id]);

                            $title = $this->translation->getTranslatedString("NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER_TITLE");
                            $message = $this->translation->getTranslatedString("NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER_MESSAGE", ['%project%' => $project->name]);
                            $path = $this->router->relativeUrlFor('timesheets_sheets', array('project' => $project->getHash())) . "?from=" . $date->format('Y-m-d') . "&to=" . $date->format('Y-m-d');

                            $this->notifications_service->sendNotificationsToUsersWithCategory("NOTIFICATION_CATEGORY_TIMESHEET_CHECK_REMINDER", $title, $message, $path, $project->id);

                            $this->settings_mapper->updateLastRun("lastRunTimesheetNotifyProject", $project->id);
                        }
                    }
                }
            }


            $this->settings_mapper->updateSetting("isCronRunning", 0);

            $response_data['result'] = 'success';
        } else {
            $response_data['result'] = 'skip';
        }


        return new Payload(Payload::$RESULT_JSON, $response_data);
    }
}
