<?php

namespace App\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;
use Dubture\Monolog\Reader\LogReader;

class MainController {

    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function getDatatableLang(Request $request, Response $response) {
        $lang = $this->ci->get('settings')['app']['i18n']['datatables'];

        $file = file_get_contents(__DIR__ . '/../lang/dataTables/' . $lang);

        /**
         * Remove comments from file
         * @see https://stackoverflow.com/a/19136663
         */
        $file = preg_replace('!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s', '', $file);

        $json = json_decode($file);

        return $response->withJson($json);
    }

    public function index(Request $request, Response $response) {
        return $this->ci->get('view')->render($response, 'main/index.twig', []);
    }

    public function login(Request $request, Response $response) {

        $user = $this->ci->get('helper')->getUser();
        // user is logged in, redirect to frontpage
        if (!is_null($user)) {
            return $response->withRedirect($this->ci->get('router')->pathFor('index'), 301);
        }

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $username = array_key_exists('username', $data) ? filter_var($data['username'], FILTER_SANITIZE_STRING) : null;
            $password = array_key_exists('password', $data) ? filter_var($data['password'], FILTER_SANITIZE_STRING) : null;

            if ($this->ci->get('helper')->checkLogin($username, $password)) {
                return $response->withRedirect($this->ci->get('router')->pathFor('index'), 301);
            }
            // redirect to login page to delete the POST Data and remove the user from the twig-view
            return $response->withRedirect($this->ci->get('router')->pathFor('login'), 301);
        }

        return $this->ci->view->render($response, 'main/login.twig', array());
    }

    public function logout(Request $request, Response $response) {

        $logger = $this->ci->get('logger');
        $logger->addInfo('LOGOUT');

        $this->ci->get('helper')->deleteSessionVar("user");

        return $response->withRedirect($this->ci->get('router')->pathFor('index'), 302);
    }

    public function cron(Request $request, Response $response) {


        $logger = $this->ci->get('logger');
        $logger->addInfo('Running CRON');

        $settings_mapper = new \App\Settings\SettingsMapper($this->ci);

        $lastRunRecurring = $settings_mapper->getSetting("lastRunRecurring");
        $lastRunFinanceSummary = $settings_mapper->getSetting("lastRunFinanceSummary");
        $lastRunCardReminder = $settings_mapper->getSetting("lastRunCardReminder");

        $date = new \DateTime('now');

        $recurring_ctrl = new \App\Finances\Recurring\Controller($this->ci);

        // Update recurring finances @ 06:00
        if ($date->format("H") === "06" && $lastRunRecurring->getDayDiff() > 0) {
            $logger->addNotice('CRON - Update Finances');

            $recurring_ctrl->update();
            $settings_mapper->updateLastRun("lastRunRecurring");
        }

        // Is first of month @ 08:00? Send Finance Summary
        if ($date->format("d") === "01" && $date->format("H") === "08" && $lastRunFinanceSummary->getDayDiff() > 0) {
            $logger->addNotice('CRON - Send Finance Summary');

            $recurring_ctrl->sendSummary();
            $settings_mapper->updateLastRun("lastRunFinanceSummary");
        }

        // card reminder @ 09:00
        if ($date->format("H") === "09" && $lastRunCardReminder->getDayDiff() > 0) {
            $logger->addNotice('CRON - Send Card Reminder');

            $card_ctrl = new \App\Board\Card\Controller($this->ci);
            $card_ctrl->reminder();
            $settings_mapper->updateLastRun("lastRunCardReminder");
        }

        return $response->withJSON(array('result' => 'success'));
    }

    public function showLog(Request $request, Response $response) {

        // GET Param 'days'
        $days = intval(filter_var($request->getQueryParam('days', 1), FILTER_SANITIZE_NUMBER_INT));

        $reader = new LogReader($this->ci->get('settings')['logger']['path'], $days);

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
                $line["hide"] = strpos($log["message"], "/dataTable") == 0 && strpos($log["message"], "?draw=2") == 0 ? false : true;

                $line["url"] = array_key_exists("url", $log['extra']) ? $log['extra']["url"] : null;

                if (strlen($line["url"]) > 100 && array_key_exists("query", $log['extra'])) {
                    $line["url"] = str_replace($log['extra']["query"], "...", $line["url"]);
                }

                $line["query"] = array_key_exists("query", $log['extra']) ? $log['extra']["query"] : null;

                array_push($logfile, $line);
            }
        }

        return $this->ci->view->render($response, 'main/logfile.twig', array("logfile" => $logfile));
    }

}
