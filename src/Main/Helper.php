<?php

namespace App\Main;

class Helper {

    private $ci;
    private $usermapper;

    public function __construct($container) {
        $this->ci = $container;
        $this->usermapper = new \App\User\Mapper($this->ci);
    }

    public function request($URL, $method = 'GET', $data = array(), $secure = true) {

        if ($method != 'POST' && $method != 'PUT' && !empty($data)) {

            $delimiter = '?';
            /**
             * If there is a GET Parameter already on the URL
             */
            if (strpos($URL, '?') !== false) {
                $delimiter = '&';
            }
            $URL = $URL . $delimiter . http_build_query($data);
        }

        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 Life-Tracking');

            //  Need to comment in for local development
            if (!$secure) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }

            if ($method == 'POST' || $method == 'PUT') {
                curl_setopt($ch, CURLOPT_POST, count($data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }

            if ($method != 'GET' && $method != 'POST') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }

            $result = curl_exec($ch);

            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
            curl_close($ch);

            return array($status_code, $result);
        } catch (Exception $e) {

            $logger = $this->ci->get('logger');
            $logger->addError("CURL", array("URL" => $URL, "method" => $method, "data" => $data, "error" => $e->getMessage()));

            print $e->getMessage();
        }
    }

    public function send_mail($template, $to, $subject = '', $body = array()) {

        $fromName = $this->ci->get('settings')['app']['mail']['fromName'];
        $fromAddress = $this->ci->get('settings')['app']['mail']['fromAddress'];

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->setFrom($fromAddress, $fromName, false);
        if (is_array($to)) {
            foreach ($to as $address) {
                $mail->addAddress($address);
            }
        } else {
            $mail->addAddress($to);
        }
        $mail->addAddress($to);
        $mail->addReplyTo($fromAddress, $fromName);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $this->ci->get('view')->fetch($template, $body);

        return $mail->send();
    }

    public function getLanguage() {
        $selected_language = $this->ci->get('settings')['app']['i18n']['template'];
        $lang = require __DIR__ . '/../lang/' . $selected_language . '.php';
        return $lang;
    }

    public function getTranslatedString($key) {
        $lang = $this->getLanguage();
        return array_key_exists($key, $lang) ? $lang[$key] : $key;
    }

    public function setUser(\App\User\User $user) {
        $_SESSION["user"] = $user;
    }

    public function getUser() {
        if (array_key_exists("user", $_SESSION)) {
            $userFromSession = $_SESSION["user"];

            // refresh user for possible changed access rights
            $user = $this->usermapper->get($userFromSession->id);
            $this->setUser($user);

            // add updated user to view
            $this->ci->get('view')->getEnvironment()->addGlobal("user", $user);

            return $user;
        }
        return null;
    }
    
    public function getUserLogin(){
        if (array_key_exists("user", $_SESSION)) {
            return $_SESSION["user"]->login;
        }
        return null;
    }

    public function setSessionVar($key, $var) {
        $_SESSION[$key] = $var;
    }

    public function getSessionVar($key, $fallback = null) {
        return array_key_exists($key, $_SESSION) ? filter_var($_SESSION[$key]) : $fallback;
    }

    public function deleteSessionVar($key) {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

    public function setPath($path) {
        $this->path = $path;
        // add path to view
        $this->ci->get('view')->getEnvironment()->addGlobal("baseURL", $path);
    }

    public function getPath() {
        return $this->path;
    }

    public function checkLogin($username = null, $password = null) {

        $logger = $this->ci->get('logger');
        $banlist = new \App\Main\BanlistMapper($this->ci);

        if (!is_null($username) && !is_null($password)) {

            try {
                $user = $this->usermapper->getUserFromLogin($username);

                if (password_verify($password, $user->password)) {
                    $this->setUser($user);
                    $banlist->deleteFailedLoginAttempts($this->getIP());

                    $logger->addNotice('LOGIN successfully', array("login" => $username));

                    return true;
                }
            } catch (\Exception $e) {
                $logger->addError('Login FAILED / User not found', array('user' => $username, 'error' => $e->getMessage()));
            }


            // wrong login!

            $this->ci->get('helper')->deleteSessionVar("user");

            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("WRONG_LOGIN"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');

            $logger->addWarning('Login WRONG', array("login" => $username));

            /**
             * Log failed login to database
             */
            if (!is_null($username) && !is_null($this->getIP())) {
                $model = new \App\Base\Model(array('ip' => $this->getIP(), 'username' => $username));
                $banlist->insert($model);
            }
        }
        return false;
    }

    public function getIP() {
        return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    }
    public function getURI() {
        return filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
    }

}
