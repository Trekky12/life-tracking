<?php

namespace App\Domain\User\Token;

use App\Domain\ObjectRemover;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class TokenRemover extends ObjectRemover {

    public function __construct(LoggerInterface $logger, CurrentUser $user, TokenMapper $mapper) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function delete($id, $user = null): Payload {
        if (!$this->isTokenOfCurrentUser($id)) {
            return new Payload(Payload::$STATUS_ERROR, 'NO_ACCESS');
        }
        return parent::delete($id, null);
    }

    public function isTokenOfCurrentUser($id) {
        $user = $this->current_user->getUser();
        $token = $this->mapper->get($id);

        if (intval($token->user) !== intval($user->id)) {
            return false;
        }

        return true;
    }

}
