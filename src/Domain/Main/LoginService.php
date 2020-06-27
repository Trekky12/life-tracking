<?php

namespace App\Domain\Main;

use App\Domain\Admin\Banlist\BanlistService;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages as Flash;
use App\Domain\Main\Translator;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;
use App\Domain\User\UserService;
use App\Domain\User\Token\TokenService;
use App\Domain\User\ApplicationPasswords\ApplicationPasswordMapper;

class LoginService {

    private $user_service;
    private $token_service;
    // cache the user object
    private $user = null;
    private $logger;
    protected $twig;
    protected $flash;
    protected $translation;
    protected $settings;
    protected $banlist_service;
    protected $current_user;
    protected $applicationpassword_mapper;

    public function __construct(LoggerInterface $logger,
            Twig $twig,
            Flash $flash,
            Settings $settings,
            Translator $translation,
            CurrentUser $current_user,
            UserService $user_service,
            TokenService $token_service,
            BanlistService $banlist_service,
            ApplicationPasswordMapper $applicationpassword_mapper) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->flash = $flash;
        $this->translation = $translation;
        $this->settings = $settings;

        $this->current_user = $current_user;

        $this->user_service = $user_service;
        $this->token_service = $token_service;

        $this->banlist_service = $banlist_service;

        $this->applicationpassword_mapper = $applicationpassword_mapper;
    }

    public function setUserFromToken($token) {
        if (!is_null($token) && $token !== FALSE) {

            try {
                $user_id = $this->token_service->getUserFromToken($token);
            } catch (\Exception $e) {
                $this->logger->error("No Token in database");

                return false;
            }

            // refresh user for possible changed access rights
            $user = $this->user_service->getEntry($user_id);
            $this->current_user->setUser($user);

            // add user object to view
            $this->twig->getEnvironment()->addGlobal("user", $user);
            $this->twig->getEnvironment()->addGlobal("user_token", $token);

            $this->token_service->updateToken($token);

            return true;
        }

        return false;
    }

    public function saveToken() {
        return $this->token_service->saveToken();
    }

    public function removeToken($token) {
        $this->logger->notice('LOGOUT');
        return $this->token_service->removeToken($token);
    }

    public function getUserLogin() {
        if (!is_null($this->user)) {
            return $this->user->login;
        }
        return null;
    }

    public function checkApplicationLogin($username = null, $password = null) {
        if (!is_null($username) && !is_null($password)) {
            $possible_passwords = $this->applicationpassword_mapper->getPasswords($username);

            foreach ($possible_passwords as $pw) {
                if (password_verify($password, $pw["pw"])) {

                    $user = $this->user_service->getUserFromLogin($username);
                    // save current user
                    $this->current_user->setUser($user);

                    // add user to view
                    $this->twig->getEnvironment()->addGlobal("user", $user);
                    $this->banlist_service->deleteFailedLoginAttempts(Utility::getIP());

                    $this->logger->notice('HTTP Application login successfully', array("login" => $username, "application" => $pw["name"]));

                    return true;
                }
            }

            // wrong login!
            $this->logger->warning('HTTP Application login WRONG', array("login" => $username));

            /**
             * Log failed login to database
             */
            if (!is_null($username) && !is_null(Utility::getIP())) {
                $this->banlist_service->addBan(Utility::getIP(), $username);
            }
        }
    }

    public function checkLogin($username = null, $password = null) {
        if (!is_null($username) && !is_null($password)) {

            try {
                $user = $this->user_service->getUserFromLogin($username);

                if (password_verify($password, $user->password)) {
                    // save current user
                    $this->current_user->setUser($user);
                    // add user to view
                    $this->twig->getEnvironment()->addGlobal("user", $user);
                    $this->banlist_service->deleteFailedLoginAttempts(Utility::getIP());

                    $this->logger->notice('LOGIN successfully', array("login" => $username));

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->error('Login FAILED / User not found', array('user' => $username, 'error' => $e->getMessage()));
            }

            // wrong login!
            $this->logger->warning('Login WRONG', array("login" => $username));

            /**
             * Log failed login to database
             */
            if (!is_null($username) && !is_null(Utility::getIP())) {
                $this->banlist_service->addBan(Utility::getIP(), $username);
            }
        }
        return false;
    }

    public function loginPage() {
        $user = $this->current_user->getUser();
        // user is logged in, redirect to frontpage
        if (!is_null($user)) {
            return $response->withRedirect($this->router->urlFor('index'), 301);
        }

        return $this->twig->render($response, 'main/login.twig', array());
    }

    public function login($data) {
        $username = array_key_exists('username', $data) ? filter_var($data['username'], FILTER_SANITIZE_STRING) : null;
        $password = array_key_exists('password', $data) ? filter_var($data['password'], FILTER_SANITIZE_STRING) : null;

        if ($this->checkLogin($username, $password)) {
            $token = $this->saveToken();

            return $token;
        }

        return false;
    }

}
