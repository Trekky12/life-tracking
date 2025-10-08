<?php

namespace App\Domain\Admin\Banlist;

use Psr\Log\LoggerInterface;
use App\Application\Payload\Payload;

class BanlistService {

    public static $MAX_ATTEMPTS_FAILED_LOGIN = 2;
    public static $MAX_ATTEMPTS_PAGE_NOT_FOUND = 10;
    private $logger;
    private $mapper;

    public function __construct(LoggerInterface $logger, BanMapper $mapper) {
        $this->logger = $logger;
        $this->mapper = $mapper;
    }

    public function unBan($ip, $with_username = true) {
        return $this->mapper->unBan($ip, $with_username);
    }

    public function addBan($ip, $username = null) {
        $ban = new \App\Domain\DataObject(array('ip' => $ip, 'username' => $username));
        $this->mapper->insert($ban);
    }

    public function isBlocked($ip) {
        return ($this->mapper->getEntries($ip, true) > self::$MAX_ATTEMPTS_FAILED_LOGIN ||
            $this->mapper->getEntries($ip, false) > self::$MAX_ATTEMPTS_PAGE_NOT_FOUND
        );
    }

    public function index() {
        $list_failed_logins = $this->mapper->getBlockedIPAdresses(self::$MAX_ATTEMPTS_FAILED_LOGIN, true);
        $list_page_not_found = $this->mapper->getBlockedIPAdresses(self::$MAX_ATTEMPTS_PAGE_NOT_FOUND, false);
        return new Payload(Payload::$RESULT_HTML, ["list" => $list_failed_logins + $list_page_not_found]);
    }
}
