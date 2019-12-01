<?php

namespace App\Main;

use Interop\Container\ContainerInterface;

class Helper {

    private $ci;
    private $user_mapper;
    private $token_mapper;
    
    // cache the user object
    private $user = null;
    
    private $logger;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->user_mapper = new \App\User\Mapper($this->ci);
        $this->token_mapper = new \App\User\Token\Mapper($this->ci);
        
        $this->logger = $this->ci->get('logger');
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
            $this->logger->addError("CURL", array("URL" => $URL, "method" => $method, "data" => $data, "error" => $e->getMessage()));

            print $e->getMessage();
        }
    }

    public function send_mail($template, $to, $subject = '', $body = array()) {
        
        $mailSettings = $this->ci->get('settings')['app']['mail'];

        $fromName = $mailSettings["fromName"];
        $fromAddress = $mailSettings["fromAddress"];

        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        
        if ($mailSettings["smtp"]) {
            $mail->IsSMTP(); // enable SMTP
            //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true; // authentication enabled
            $mail->SMTPSecure = $mailSettings["secure"];
            $mail->Host = $mailSettings["host"];
            $mail->Port = $mailSettings["port"];
            $mail->Username = $mailSettings["username"];
            $mail->Password = $mailSettings["password"];
        }
        
        $mail->setFrom($fromAddress, $fromName, false);
        if (is_array($to)) {
            foreach ($to as $address) {
                $mail->addAddress($address);
            }
        } else {
            $mail->addAddress($to);
        }
        
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
        $this->user = $this->user_mapper->get($user_id);
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
                $user_id = $this->token_mapper->getUserFromToken($token);
            } catch (\Exception $e) {
                $this->logger->addError("No Token in database");

                return false;
            }

            // refresh user for possible changed access rights
            $this->user = $this->user_mapper->get($user_id);

            // add user object to view
            $this->ci->get('view')->getEnvironment()->addGlobal("user", $this->user);
            $this->ci->get('view')->getEnvironment()->addGlobal("user_token", $token);

            $this->token_mapper->updateTokenData($token, $this->getIP(), $this->getAgent());

            return true;
        }

        return false;
    }

    public function saveToken() {
        $user = $this->getUser();
        if (!is_null($user)) {
            $secret = $this->ci->get('settings')['app']['secret'];
            $token = hash('sha512', $secret . time() . $user->id);
            $this->token_mapper->addToken($user->id, $token, $this->getIP(), $this->getAgent());
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
        $banlistCtrl = new \App\Banlist\Controller($this->ci);

        if (!is_null($username) && !is_null($password)) {

            try {
                $user = $this->user_mapper->getUserFromLogin($username);

                if (password_verify($password, $user->password)) {
                    $this->setUser($user->id);
                    $banlistCtrl->deleteFailedLoginAttempts($this->getIP());

                    $this->logger->addNotice('LOGIN successfully', array("login" => $username));

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->addError('Login FAILED / User not found', array('user' => $username, 'error' => $e->getMessage()));
            }


            // wrong login!
            $this->ci->get('flash')->addMessage('message', $this->ci->get('helper')->getTranslatedString("WRONG_LOGIN"));
            $this->ci->get('flash')->addMessage('message_type', 'danger');

            $this->logger->addWarning('Login WRONG', array("login" => $username));

            /**
             * Log failed login to database
             */
            if (!is_null($username) && !is_null($this->getIP())) {
                $banlistCtrl->addBan($this->getIP(), $username);
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

    public function getDateRange($data, $defaultFrom = 'today', $defaultTo = 'today') {

        if (strcmp($defaultFrom, 'today') === 0 ) {
            $defaultFrom = date('Y-m-d');
        }
        if (strcmp($defaultTo, 'today') === 0) {
            $defaultTo = date('Y-m-d');
        }

        $from = array_key_exists('from', $data) && !empty($data['from']) ? filter_var($data['from'], FILTER_SANITIZE_STRING) : $defaultFrom;
        $to = array_key_exists('to', $data) && !empty($data['to']) ? filter_var($data['to'], FILTER_SANITIZE_STRING) : $defaultTo;

        /**
         * Clean dates
         */
        $dateRegex = "/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/";
        if (!preg_match($dateRegex, $from) || !preg_match($dateRegex, $to)) {

            $from = preg_match($dateRegex, $from) ? $from : $defaultFrom;
            $to = preg_match($dateRegex, $to) ? $to : $defaultTo;
        }

        return array($from, $to);
    }

    public function getRequestURI(\Psr\Http\Message\RequestInterface $request) {
        $requestURI = $request->getUri();
        $path = $requestURI->getPath();
        $params = $requestURI->getQuery();
        $uri = strlen($params) > 0 ? $path . '?' . $params : $path;
        return $uri;
    }
    
    public function getMonthName($month) {
        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['month_name']);

        $dateObj = \DateTime::createFromFormat('!m', $month);
        return $fmt->format($dateObj);
    }
    
    public function getDay($date) {
        $langugage = $this->ci->get('settings')['app']['i18n']['php'];
        $dateFormatPHP = $this->ci->get('settings')['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($langugage, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['date']);

        $dateObj = $d = new \DateTime($date);
        return $fmt->format($dateObj);
    }
    
    public function splitDateInterval($total_seconds) {
        $total_minutes = $total_seconds / 60;
        $hours = intval($total_minutes / 60);
        $minutes = intval($total_minutes - $hours * 60);
        $seconds = intval($total_seconds - $total_minutes * 60);

        return !is_null($total_seconds) ? sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) : '';
    }

}
