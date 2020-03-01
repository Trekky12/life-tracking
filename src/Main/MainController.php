<?php

namespace App\Main;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Main\Helper;
use App\Main\UserHelper;
use App\Activity\Controller as Activity;
use Slim\Flash\Messages as Flash;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use Slim\Csrf\Guard as CSRF;
use App\Base\Settings;
use App\Base\CurrentUser;
use Dubture\Monolog\Reader\LogReader;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;

class MainController extends \App\Base\Controller {

    protected $csrf;
    protected $settings_mapper;
    protected $recurring_ctrl;
    protected $card_ctrl;
    protected $token_ctrl;
    protected $user_helper;
    protected $current_user;

    public function __construct(LoggerInterface $logger, Twig $twig, Helper $helper, Flash $flash, RouteParser $router, Settings $settings, \PDO $db, Activity $activity, Translator $translation, UserHelper $user_helper, CurrentUser $current_user, CSRF $csrf) {
        parent::__construct($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);
        $this->csrf = $csrf;
        $this->user_helper = $user_helper;
        $this->current_user = $current_user;

        $this->settings_mapper = new \App\Settings\SettingsMapper($this->db, $this->translation);
        $this->recurring_ctrl = new \App\Finances\Recurring\Controller($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);
        $this->card_ctrl = new \App\Board\Card\Controller($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);
        $this->token_ctrl = new \App\User\Token\Controller($logger, $twig, $helper, $flash, $router, $settings, $db, $activity, $translation, $current_user);
    }

    public function index(Request $request, Response $response) {
        $pwa = $request->getQueryParam('pwa', null);
        // is PWA? redirect to start page
        if (!is_null($pwa)) {
            $user = $this->current_user->getUser();
            if (!is_null($user) && !empty($user->start_url)) {
                return $response->withRedirect($user->start_url, 301);
            }
        }

        return $this->twig->render($response, 'main/index.twig', []);
    }

    public function login(Request $request, Response $response) {

        $user = $this->current_user->getUser();
        // user is logged in, redirect to frontpage
        if (!is_null($user)) {
            return $response->withRedirect($this->router->urlFor('index'), 301);
        }

        if ($request->isPost()) {

            $data = $request->getParsedBody();
            $username = array_key_exists('username', $data) ? filter_var($data['username'], FILTER_SANITIZE_STRING) : null;
            $password = array_key_exists('password', $data) ? filter_var($data['password'], FILTER_SANITIZE_STRING) : null;

            if ($this->user_helper->checkLogin($username, $password)) {
                $token = $this->user_helper->saveToken();

                // add token to cookie
                $cookie = SetCookie::create('token')
                        ->withValue($token)
                        ->rememberForever();

                $response = FigResponseCookies::set($response, $cookie);

                return $response->withRedirect($this->router->urlFor('index'), 301);
            }

            // redirect to logout to delete the POST Data and remove the user from the twig-view
            return $this->logout($request, $response);
            //return $response->withRedirect($this->router->urlFor('login'), 301);
        }

        return $this->twig->render($response, 'main/login.twig', array());
    }

    public function logout(Request $request, Response $response) {
        $this->logger->addNotice('LOGOUT');

        // remove token from database and cookies
        $token = FigRequestCookies::get($request, 'token');
        $this->user_helper->removeToken($token->getValue());
        $response = FigResponseCookies::expire($response, 'token');

        return $response->withRedirect($this->router->urlFor('login'), 302);
    }

    public function cron(Request $request, Response $response) {

        $this->logger->addInfo('Running CRON');

        $lastRunRecurring = $this->settings_mapper->getSetting("lastRunRecurring");
        $lastRunFinanceSummary = $this->settings_mapper->getSetting("lastRunFinanceSummary");
        $lastRunCardReminder = $this->settings_mapper->getSetting("lastRunCardReminder");

        $date = new \DateTime('now');

        // Update recurring finances @ 06:00
        if ($date->format("H") === "06" && $lastRunRecurring->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Update Finances');

            $this->recurring_ctrl->update();
            $this->settings_mapper->updateLastRun("lastRunRecurring");
        }

        // Is first of month @ 08:00? Send Finance Summary
        if ($date->format("d") === "01" && $date->format("H") === "08" && $lastRunFinanceSummary->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Send Finance Summary');

            $this->recurring_ctrl->sendSummary();
            $this->settings_mapper->updateLastRun("lastRunFinanceSummary");
        }

        // card reminder @ 09:00
        if ($date->format("H") === "09" && $lastRunCardReminder->getDayDiff() > 0) {
            $this->logger->addNotice('CRON - Send Card Reminder');

            $this->card_ctrl->reminder();
            $this->settings_mapper->updateLastRun("lastRunCardReminder");

//            $this->token_ctrl->deleteOldTokens();
        }

        $response_data = ['result' => 'success'];
        return $response->withJSON($response_data);
    }

    public function showLog(Request $request, Response $response) {

        // GET Param 'days'
        $days = intval(filter_var($request->getQueryParam('days', 1), FILTER_SANITIZE_NUMBER_INT));

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

        return $this->twig->render($response, 'main/logfile.twig', array("logfile" => $logfile));
    }

    public function getCSRFTokens(Request $request, Response $response) {

        $data = $request->getParsedBody();
        $count = array_key_exists('count', $data) ? intval(filter_var($data['count'], FILTER_SANITIZE_NUMBER_INT)) : 5;

        $tokens = [];
        for ($i = 0; $i < $count; $i++) {
            $tokens[] = $this->csrf->generateToken();
        }

        return $response->withJson($tokens);
    }

}
