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

class CronService extends Service {

    protected $settings_mapper;
    protected $finances_entry_creator;
    protected $finance_stats_monthly_mail_service;
    protected $card_mail_service;
    protected $token_service;
    private $bill_entry_creator;

    public function __construct(LoggerInterface $logger,
            CurrentUser $user,
            SettingsMapper $settings_mapper,
            RecurringFinanceEntryCreator $finances_entry_creator,
            FinanceStatsMonthlyMailService $finance_stats_monthly_mail_service,
            CardMailService $card_mail_service,
            TokenService $token_service,
            RecurringBillEntryCreator $bill_entry_creator) {
        parent::__construct($logger, $user);

        $this->settings_mapper = $settings_mapper;
        $this->finances_entry_creator = $finances_entry_creator;
        $this->finance_stats_monthly_mail_service = $finance_stats_monthly_mail_service;
        $this->card_mail_service = $card_mail_service;
        $this->bill_entry_creator = $bill_entry_creator;
        $this->token_service = $token_service;
    }

    public function cron(): Payload {
        $this->logger->info('Running CRON');

        $lastRunRecurring = $this->settings_mapper->getSetting("lastRunRecurring");
        $lastRunFinanceSummary = $this->settings_mapper->getSetting("lastRunFinanceSummary");
        $lastRunCardReminder = $this->settings_mapper->getSetting("lastRunCardReminder");
        $lastRunRecurringSplitbills = $this->settings_mapper->getSetting("lastRunRecurringSplitbills");

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

        $response_data = ['result' => 'success'];

        return new Payload(Payload::$RESULT_JSON, $response_data);
    }

}
