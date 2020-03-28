<?php

namespace App\Domain\Admin\Banlist;

use App\Domain\ObjectRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class BanRemover extends ObjectRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, BanMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function delete($id, $user = null): Payload {
        return parent::delete($id, $user);
    }

    /**
     * Use IP instead of ID for input
     * manually create activity
     * @param type $ip
     * @return type
     */
    public function deleteEntry($ip) {
        return $this->mapper->deleteFailedLoginAttempts($ip);
    }

}
