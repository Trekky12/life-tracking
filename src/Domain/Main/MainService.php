<?php

namespace App\Domain\Main;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;
use Slim\Csrf\Guard as CSRF;

class MainService extends Service {

    private $csrf;

    public function __construct(LoggerInterface $logger, CurrentUser $user, CSRF $csrf) {
        parent::__construct($logger, $user);
        $this->csrf = $csrf;
    }

    public function getCSRFTokens($count) {
        $tokens = [];
        for ($i = 0; $i < $count; $i++) {
            $tokens[] = $this->csrf->generateToken();
        }

        return new Payload(Payload::$RESULT_JSON, $tokens);
    }

}
