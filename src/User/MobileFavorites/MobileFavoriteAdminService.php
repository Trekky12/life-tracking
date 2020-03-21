<?php

namespace App\User\MobileFavorites;

use Psr\Log\LoggerInterface;
use App\Activity\Controller as Activity;
use App\Main\Translator;
use Slim\Routing\RouteParser;
use App\Base\Settings;
use App\Base\CurrentUser;
use App\User\UserService;

class MobileFavoriteAdminService extends MobileFavoriteService {

    protected $dataobject_parent = \App\User\User::class;
    protected $element_view_route = 'users_mobile_favorites_edit_admin';

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper,
            UserService $user_service) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user, $mapper);
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

    protected function getElementViewRoute($entry) {
        $this->element_view_route_params["user"] = $entry->user;
        return parent::getElementViewRoute($entry);
    }

    protected function getParentObjectService() {
        return $this->user_service;
    }

}
