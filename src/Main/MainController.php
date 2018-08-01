<?php

namespace App\Main;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Interop\Container\ContainerInterface;

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
        return $this->ci->get('view')->render($response, 'index.twig', []);
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

        return $this->ci->view->render($response, 'login.twig', array());
    }

    public function logout(Request $request, Response $response) {

        $logger = $this->ci->get('logger');
        $info = $this->ci->get('info');
        $logger->addInfo('LOGOUT', $info);

        $this->ci->get('helper')->deleteSessionVar("user");

        return $response->withRedirect($this->ci->get('router')->pathFor('index'), 302);
    }

    public function cron(Request $request, Response $response) {

        $settings_mapper = new \App\Settings\SettingsMapper($this->ci);

        $lastRunMonthly = $settings_mapper->getSetting("lastRunMonthly");
        $lastRunFinanceSummary = $settings_mapper->getSetting("lastRunFinanceSummary");
        $lastRunCardReminder = $settings_mapper->getSetting("lastRunCardReminder");

        $date = new \DateTime('now');

        $monthly_ctrl = new \App\Finances\Monthly\Controller($this->ci);

        // Update monthly finances @ 06:00
        if ($date->format("H") === "06" && $lastRunMonthly->getDayDiff() > 0) {
            $monthly_ctrl->update();
            $settings_mapper->updateLastRun("lastRunMonthly");
        }

        // Is first of month @ 08:00? Send Finance Summary
        if ($date->format("d") === "01" && $date->format("H") === "08" && $lastRunFinanceSummary->getDayDiff() > 0) {
            $monthly_ctrl->sendSummary();
            $settings_mapper->updateLastRun("lastRunFinanceSummary");
        }

        // card reminder @ 09:00
        if ($date->format("H") === "09" && $lastRunCardReminder->getDayDiff() > 0) {
            $card_ctrl = new \App\Board\Card\Controller($this->ci);
            $card_ctrl->reminder();
            $settings_mapper->updateLastRun("lastRunCardReminder");
        }

        return $response->withJSON(array('result' => 'success'));
    }

}
