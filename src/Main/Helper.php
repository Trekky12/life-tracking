<?php

namespace App\Main;

use Psr\Container\ContainerInterface;

class Helper {

    private $logger;
    protected $twig;
    protected $settings;
    
    private $baseURL;

    public function __construct(ContainerInterface $ci) {
        $this->logger = $ci->get('logger');
        $this->twig = $ci->get('view');

        $this->settings = $ci->get('settings');
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

        $mailSettings = $this->settings['app']['mail'];

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

        $mail->Body = $this->twig->fetch($template, $body);

        return $mail->send();
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

    public function setBaseURL($baseURL) {
        $this->baseURL = $baseURL;
        // add base URL to view
        $this->twig->getEnvironment()->addGlobal("baseURL", $baseURL);
    }

    public function getBaseURL() {
        return $this->baseURL;
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

        if (strcmp($defaultFrom, 'today') === 0) {
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
        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
        $fmt->setPattern($dateFormatPHP['month_name']);

        $dateObj = \DateTime::createFromFormat('!m', $month);
        return $fmt->format($dateObj);
    }

    public function getDay($date) {
        $language = $this->settings['app']['i18n']['php'];
        $dateFormatPHP = $this->settings['app']['i18n']['dateformatPHP'];

        $fmt = new \IntlDateFormatter($language, NULL, NULL);
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
