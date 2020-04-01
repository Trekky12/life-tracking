<?php

namespace App\Domain\User\Token;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class TokenAdminService extends GeneralService {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, TokenMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function index() {
        $list = $this->mapper->getAll();
        $users = $this->user_service->getAll();
        return new Payload(Payload::$RESULT_HTML, ['list' => $list, 'users' => $users]);
    }

}
