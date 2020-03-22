<?php

namespace App\Domain\Main;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;

class Helper {

    private $logger;
    protected $twig;
    protected $settings;
    private $baseURL;

    public function __construct(LoggerInterface $logger, Twig $twig, Settings $settings) {
        $this->logger = $logger;
        $this->twig = $twig;

        $this->settings = $settings;
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

        $mailSettings = $this->settings->getAppSettings()['mail'];

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

    public function setBaseURL($baseURL) {
        $this->baseURL = $baseURL;
        // add base URL to view
        $this->twig->getEnvironment()->addGlobal("baseURL", $baseURL);
    }

    public function getBaseURL() {
        return $this->baseURL;
    }

}
