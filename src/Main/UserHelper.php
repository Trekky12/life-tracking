<?php

namespace App\Main;

use Psr\Container\ContainerInterface;
use App\Banlist\Controller as BanListController;

class UserHelper {

    private $user_mapper;
    private $token_mapper;
    // cache the user object
    private $user = null;
    private $logger;
    protected $twig;
    protected $flash;
    protected $helper;
    protected $translation;
    protected $settings;
    protected $banlistCtrl;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->twig = $ci->get('view');
        $this->flash = $ci->get('flash');
        $this->helper = $ci->get('helper');
        $this->translation = $ci->get('translation');
        $this->settings = $ci->get('settings');
        
        $db = $ci->get('db');
        $translation = $ci->get('translation');
        
        $this->user_mapper = new \App\User\Mapper($db, $translation);
        $this->token_mapper = new \App\User\Token\Mapper($db, $translation);

        $this->banlistCtrl = new BanListController($ci);
    }

    public function setUser($user_id) {
        // cache the user
        $this->user = $this->user_mapper->get($user_id);
        // add user to view
        $this->twig->getEnvironment()->addGlobal("user", $this->user);
    }

    public function getUser() {
        // get cached user object
        if (!is_null($this->user)) {
            return $this->user;
        }
        return null;
    }

    public function setUserFromToken($token) {
        if (!is_null($token) && $token !== FALSE) {

            try {
                $user_id = $this->token_mapper->getUserFromToken($token);
            } catch (\Exception $e) {
                $this->logger->addError("No Token in database");

                return false;
            }

            // refresh user for possible changed access rights
            $this->user = $this->user_mapper->get($user_id);

            // add user object to view
            $this->twig->getEnvironment()->addGlobal("user", $this->user);
            $this->twig->getEnvironment()->addGlobal("user_token", $token);

            $this->token_mapper->updateTokenData($token, $this->helper->getIP(), $this->helper->getAgent());

            return true;
        }

        return false;
    }

    public function saveToken() {
        $user = $this->getUser();
        if (!is_null($user)) {
            $secret = $this->settings['app']['secret'];
            $token = hash('sha512', $secret . time() . $user->id);
            $this->token_mapper->addToken($user->id, $token, $this->helper->getIP(), $this->helper->getAgent());
            return $token;
        }
        return null;
    }

    public function removeToken($token) {
        if (!is_null($token) && $token !== FALSE) {
            $this->token_mapper->deleteToken($token);
        }
    }

    public function getUserLogin() {
        if (!is_null($this->user)) {
            return $this->user->login;
        }
        return null;
    }

    public function checkLogin($username = null, $password = null) {
        if (!is_null($username) && !is_null($password)) {

            try {
                $user = $this->user_mapper->getUserFromLogin($username);

                if (password_verify($password, $user->password)) {
                    $this->setUser($user->id);
                    $this->banlistCtrl->deleteFailedLoginAttempts($this->helper->getIP());

                    $this->logger->addNotice('LOGIN successfully', array("login" => $username));

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->addError('Login FAILED / User not found', array('user' => $username, 'error' => $e->getMessage()));
            }


            // wrong login!
            $this->flash->addMessage('message', $this->translation->getTranslatedString("WRONG_LOGIN"));
            $this->flash->addMessage('message_type', 'danger');

            $this->logger->addWarning('Login WRONG', array("login" => $username));

            /**
             * Log failed login to database
             */
            if (!is_null($username) && !is_null($this->helper->getIP())) {
                $this->banlistCtrl->addBan($this->helper->getIP(), $username);
            }
        }
        return false;
    }

}
