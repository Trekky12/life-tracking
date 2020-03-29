<?php

namespace App\Domain\Admin\Banlist;

use Psr\Log\LoggerInterface;
use App\Application\Payload\Payload;

class BanlistService {

    public static $MAX_ATTEMPTS = 2;
    private $logger;
    private $mapper;

    public function __construct(LoggerInterface $logger, BanMapper $mapper) {
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    public function getBlockedIPAdresses() {
        return $this->mapper->getBlockedIPAdresses(self::$MAX_ATTEMPTS);
    }

    public function deleteFailedLoginAttempts($ip) {
        return $this->mapper->deleteFailedLoginAttempts($ip);
    }

    public function getFailedLoginAttempts($ip) {
        return $this->mapper->getFailedLoginAttempts($ip);
    }

    public function addBan($ip, $username) {
        $ban = new \App\Domain\DataObject(array('ip' => $ip, 'username' => $username));
        $this->mapper->insert($ban);
    }

    public function isBlocked($ip) {
        return $this->getFailedLoginAttempts($ip) > self::$MAX_ATTEMPTS;
    }

    public function index() {
        $list = $this->getBlockedIPAdresses();
        return new Payload(Payload::$RESULT_HTML, ["list" => $list]);
    }

}
