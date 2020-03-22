<?php

namespace App\Domain\Main;

use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Main\LoginService;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use Slim\Csrf\Guard as CSRF;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use App\Domain\Base\CurrentUser;

class MainController extends \App\Domain\Base\Controller {

    protected $csrf;
    protected $login_service;
    private $current_user;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            RouteParser $router,
            Translator $translation,
            MainService $service,
            LoginService $login_service,
            CurrentUser $current_user,
            CSRF $csrf) {
        parent::__construct($logger, $flash, $translation);
        $this->twig = $twig;
        $this->router = $router;

        $this->service = $service;
        $this->login_service = $login_service;
        $this->csrf = $csrf;
        $this->current_user = $current_user;
    }

    public function index(Request $request, Response $response) {
        $pwa = $request->getQueryParam('pwa', null);

        $user_start_page = $this->service->getUserStartPage();
        // is PWA? redirect to start page
        if (!is_null($pwa) && !is_null($user_start_page)) {
            return $response->withRedirect($user_start_page, 301);
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

            if ($this->login_service->checkLogin($username, $password)) {
                $token = $this->login_service->saveToken();

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
        $this->login_service->removeToken($token->getValue());
        $response = FigResponseCookies::expire($response, 'token');

        return $response->withRedirect($this->router->urlFor('login'), 302);
    }

    public function cron(Request $request, Response $response) {

        $response_data = $this->service->cron();

        return $response->withJSON($response_data);
    }

    public function showLog(Request $request, Response $response) {

        // GET Param 'days'
        $days = intval(filter_var($request->getQueryParam('days', 1), FILTER_SANITIZE_NUMBER_INT));

        $logfile = $this->service->getLogfile($days);

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
