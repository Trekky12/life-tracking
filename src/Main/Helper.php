<?php

namespace App\Main;

class Helper {

    private $ci;
    private $usermapper;
    private $tokenmapper;
    // cache the user object
    private $user_id = null;
    private $user = null;

    public function __construct($container) {
        $this->ci = $container;
        $this->usermapper = new \App\User\Mapper($this->ci);
        $this->tokenmapper = new \App\User\Token\Mapper($this->ci);
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

    public function setUser($user_id) {
        // cache the user
        $this->user = $this->usermapper->get($user_id);
        // add user to view
        $this->ci->get('view')->getEnvironment()->addGlobal("user", $this->user);
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
                $user_id = $this->tokenmapper->getUserFromToken($token);
            } catch (\Exception $e) {
                $logger = $this->ci->get('logger');
                $logger->addError("No Token in database");

                return false;
            }

            // refresh user for possible changed access rights
            $this->user = $this->usermapper->get($user_id);

            // add user object to view
            $this->ci->get('view')->getEnvironment()->addGlobal("user", $this->user);

            $this->tokenmapper->updateTokenData($token, $this->getIP(), $this->getAgent());

            return true;
        }

        return false;
    }

    public function saveToken() {
        $user = $this->getUser();
        if (!is_null($user)) {
            $secret = $this->ci->get('settings')['app']['secret'];
            $token = hash('sha512', $secret . time() . $user->id);
            $this->tokenmapper->addToken($user->id, $token, $this->getIP(), $this->getAgent());
            return $token;
        }
        return null;
    }

    public function removeToken($token) {
        if (!is_null($token) && $token !== FALSE) {
            $this->tokenmapper->deleteToken($token);
        }
    }

    public function getUserLogin() {
        if (!is_null($this->user)) {
            return $this->user->login;
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
                    $this->setUser($user->id);
                    $banlist->deleteFailedLoginAttempts($this->getIP());

                    $logger->addNotice('LOGIN successfully', array("login" => $username));

                    return true;
                }
            } catch (\Exception $e) {
                $logger->addError('Login FAILED / User not found', array('user' => $username, 'error' => $e->getMessage()));
            }


            // wrong login!
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

    public function getAgent() {
        return filter_input(INPUT_SERVER, 'HTTP_USER_AGENT', FILTER_SANITIZE_STRING);
    }

    public function getDateRange($data) {

        $from = array_key_exists('from', $data) && !empty($data['from']) ? filter_var($data['from'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $to = array_key_exists('to', $data) && !empty($data['to']) ? filter_var($data['to'], FILTER_SANITIZE_STRING) : date('Y-m-d');

        /**
         * Clean dates
         */
        $dateRegex = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/";
        if (!preg_match($dateRegex, $from) || !preg_match($dateRegex, $to)) {

            $from = preg_match($dateRegex, $from) ? $from : date('Y-m-d');
            $to = preg_match($dateRegex, $to) ? $to : date('Y-m-d');
        }

        return array($from, $to);

    public function getRequestURI(\Psr\Http\Message\RequestInterface $request) {
        $requestURI = $request->getUri();
        $path = $requestURI->getPath();
        $params = $requestURI->getQuery();
        $uri = strlen($params) > 0 ? $path . '?' . $params : $path;
        return $uri;
    }

}
