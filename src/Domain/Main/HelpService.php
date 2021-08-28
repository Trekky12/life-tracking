<?php

namespace App\Domain\Main;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class HelpService extends Service {

    public function __construct(LoggerInterface $logger, CurrentUser $user) {
        parent::__construct($logger, $user);
    }

    public function getHelpPage() {
        return new Payload(Payload::$RESULT_HTML, []);
    }

}
