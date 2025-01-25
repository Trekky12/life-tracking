<?php

namespace App\Domain\Main;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use Slim\Csrf\Guard as CSRF;

class LogService extends Service {

    private $settings;

    public function __construct(LoggerInterface $logger, CurrentUser $user, Settings $settings) {
        parent::__construct($logger, $user);

        $this->settings = $settings;
    }

    public function getLogfileOverview($days) {
        return new Payload(Payload::$RESULT_HTML, ["days" => $days]);
    }

    public function getLogfile($days) {
        $file = new \SplFileObject($this->settings->all()['logger']['path'], 'r');

        $pattern = '/\[(?P<date>.*)\] (?P<logger>[\w\-]+).(?P<level>\w+): (?P<message>[^\{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';

        $logfile = array();

        while (!$file->eof()) {

            $logline = $file->current();
            $log = $this->parseLogLine($logline, $days, $pattern);

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
            $file->next();
        }

        return new Payload(Payload::$RESULT_HTML, ["logfile" => $logfile]);
    }

    private function parseLogLine($log, $days, $pattern) {
        if (!is_string($log) || strlen($log) === 0 || is_null($log)) {
            return array();
        }

        $data = [];
        preg_match($pattern, $log, $data);

        if (!isset($data['date'])) {
            return array();
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $data['date']);
        // try monolog 2 date format
        if (!$date) {
            $date = \DateTime::createFromFormat('Y-m-d\TH:i:s.uP', $data['date']);
        }

        $array = array(
            'date' => $date,
            'logger' => $data['logger'],
            'level' => $data['level'],
            'message' => $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true)
        );

        if (0 === $days) {
            return $array;
        }

        if (isset($date) && $date instanceof \DateTime) {
            $d2 = new \DateTime('now');

            if ($date->diff($d2)->days < $days) {
                return $array;
            } else {
                return array();
            }
        }
    }

}
