<?php

namespace App\Domain\User\Token;

use App\Domain\ObjectRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class TokenAdminRemover extends ObjectRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, TokenMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function delete($id, $additionalData = null): Payload {
        return parent::delete($id, $additionalData);
    }

}
