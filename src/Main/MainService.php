<?php

namespace App\Main;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use Dubture\Monolog\Reader\LogReader;
use \App\Finances\Recurring\RecurringService;
use \App\Board\Card\CardService;
use \App\User\Token\TokenService;

class MainService extends \App\Base\Service {

    protected $settings_mapper;
    protected $recurring_service;
    protected $card_service;
    protected $token_service;

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            \App\Settings\SettingsMapper $settings_mapper,
            RecurringService $recurring_service,
            CardService $card_service,
            TokenService $token_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->settings_mapper = $settings_mapper;
        $this->recurring_service = $recurring_service;
        $this->card_service = $card_service;
        $this->token_service = $token_service;
    }

    public function getUserStartPage() {
        $user = $this->current_user->getUser();
        if (!is_null($user) && !empty($user->start_url)) {
            return $user->start_url;
        }
        return null;
    }

    public function cron() {
        $this->logger->addInfo('Running CRON');

        $lastRunRecurring = $this->settings_mapper->getSetting("lastRunRecurring");
        $lastRunFinanceSummary = $this->settings_mapper->getSetting("lastRunFinanceSummary");
        $lastRunCardReminder = $this->settings_mapper->getSetting("lastRunCardReminder");

        $date = new \DateTime('now');

        // Update recurring finances @ 06:00
        if ($date->format("H") === "06" && $lastRunRecurring->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Update Finances');

            $this->recurring_service->update();
            $this->settings_mapper->updateLastRun("lastRunRecurring");
        }

        // Is first of month @ 08:00? Send Finance Summary
        if ($date->format("d") === "01" && $date->format("H") === "08" && $lastRunFinanceSummary->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Send Finance Summary');

            $this->recurring_service->sendSummary();
            $this->settings_mapper->updateLastRun("lastRunFinanceSummary");
        }

        // card reminder @ 09:00
        if ($date->format("H") === "09" && $lastRunCardReminder->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Send Card Reminder');

            $this->card_service->sendReminder();
            $this->settings_mapper->updateLastRun("lastRunCardReminder");

//            $this->token_service->deleteOldTokens();
        }

        $response_data = ['result' => 'success'];

        return $response_data;
    }

    public function getLogfile($days) {
        $reader = new LogReader($this->settings->all()['logger']['path'], $days);

        /**
         * We have a minus in the logger-name so we need a custom pattern
         */
        //$pattern = '/\[(?P<date>.*)\] (?P<logger>[\w\-]+).(?P<level>\w+): (?P<message>[^\[\{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';
        $pattern = '/\[(?P<date>.*)\] (?P<logger>[\w\-]+).(?P<level>\w+): (?P<message>[^\{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';
        $reader->getParser()->registerPattern('life-tracking', $pattern);
        $reader->setPattern('life-tracking');

        $logfile = array();

        foreach ($reader as $log) {

            // do not show datatable queries
            if (!empty($log)) {

                $line = array();

                /**
                 * Get Username
                 */
                $user = "";
                if (array_key_exists("user", $log["extra"]) && !is_null($log["extra"]["user"])) {
                    $user = $log["extra"]["user"];
                } elseif (array_key_exists("login", $log["context"])) {
                    $user = $log["context"]["login"];
                }

                // Remove surrounding "Array ( ... )'
                $regex = '/^Array\s*\((.*)?\)\s*$/s';

                $line["user"] = $user;
                $line["date"] = $log['date']->format('Y-m-d H:i:s');
                $line["level"] = $log['level'];
                $line["message"] = $log['message'];
                $line["extra"] = preg_replace($regex, '$1', print_r($log['extra'], true));
                $line["context"] = !empty($log["context"]) ? preg_replace($regex, '$1', print_r($log['context'], true)) : null;
                $line["url"] = array_key_exists("url", $log['extra']) ? $log['extra']["url"] : null;
                $line["hide"] = strpos($line["url"], "datatable=1") == 0 ? false : true;

                if (strlen($line["url"]) > 100 && array_key_exists("query", $log['extra'])) {
                    $line["url"] = str_replace($log['extra']["query"], "...", $line["url"]);
                }

                $line["query"] = array_key_exists("query", $log['extra']) ? $log['extra']["query"] : null;

                array_push($logfile, $line);
            }
        }

        return $logfile;
    }

}
