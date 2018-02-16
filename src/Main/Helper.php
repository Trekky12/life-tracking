<?php

namespace App\Main;

class Helper {

    private $container;
    private $user;

    public function __construct($container) {
        $this->container = $container;
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
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

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
            print $e->getMessage();
        }
    }

    public function send_mail($template, $to, $subject = '', $body = array()) {
        $fromName = $this->container->get('settings')['app']['mail']['fromName'];
        $fromAddress = $this->container->get('settings')['app']['mail']['fromAddress'];

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

        $mail->Body = $this->container->get('view')->fetch($template, $body);

        return $mail->send();
    }

    public function getLanguage() {
        $selected_language = $this->container->get('settings')['app']['i18n']['template'];
        $lang = require __DIR__ . '/../lang/' . $selected_language . '.php';
        return $lang;
    }

    public function getTranslatedString($key) {
        $lang = $this->getLanguage();
        return array_key_exists($key, $lang) ? $lang[$key] : $key;
    }

    public function setUser(\App\User\User $user) {
        $this->user = $user;
        $this->container->get('view')->getEnvironment()->addGlobal("user", $this->user);
    }

    public function getUser() {
        return $this->user;
    }

}
