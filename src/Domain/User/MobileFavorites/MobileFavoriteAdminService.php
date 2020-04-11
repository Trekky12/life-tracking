<?php

namespace App\Domain\User\MobileFavorites;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Domain\User\UserService;
use App\Application\Payload\Payload;

class MobileFavoriteAdminService extends Service {

    private $user_service;

    public function __construct(LoggerInterface $logger, CurrentUser $user, MobileFavoritesMapper $mapper, UserService $user_service) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->user_service = $user_service;
    }

    public function setUserForMapper($user_id) {
        $user = null;
        if (!is_null($user_id)) {
            $user = $this->user_service->getEntry($user_id);
            $this->mapper->setUser($user->id);
        }

        return $user;
    }

    public function index($user_id) {
        $user = $this->setUserForMapper($user_id);
        $favorites = $this->mapper->getAll('position');
        return new Payload(Payload::$RESULT_HTML, ['list' => $favorites, 'for_user' => $user]);
    }

    public function edit($user_id, $entry_id) {
        $user = $this->setUserForMapper($user_id);
        $entry = $this->getEntry($entry_id);
        return new Payload(Payload::$RESULT_HTML, ['entry' => $entry, 'for_user' => $user]);
    }

}
