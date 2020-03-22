<?php

namespace App\Domain\User\Token;

use Psr\Log\LoggerInterface;
use App\Domain\Activity\Controller as Activity;
use App\Domain\Main\Translator;
use Slim\Routing\RouteParser;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;

class TokenService extends \App\Domain\Service {

    public function __construct(LoggerInterface $logger,
            Translator $translation,
            Settings $settings,
            Activity $activity,
            RouteParser $router,
            CurrentUser $user,
            Mapper $mapper) {
        parent::__construct($logger, $translation, $settings, $activity, $router, $user);

        $this->mapper = $mapper;
    }

    public function getLoginTokens() {
        return $this->mapper->getAll();
    }

    public function deleteOldTokens() {
        return $this->mapper->deleteOldTokens();
    }

    public function getTokensOfCurrentUser() {
        // only tokens of current user
        $user = $this->current_user->getUser();
        $this->mapper->setSelectFilterForUser($user);

        return $this->mapper->getAll();
    }

    public function isTokenOfCurrentUser($id) {
        $user = $this->current_user->getUser();
        $token = $this->mapper->get($id);

        if (intval($token->user) !== intval($user->id)) {
            return false;
        }

        return true;
    }

    public function getUserFromToken($token) {
        return $this->mapper->getUserFromToken($token);
    }

    public function updateToken($token) {
        return $this->mapper->updateTokenData($token, Utility::getIP(), Utility::getAgent());
    }

    public function saveToken() {
        $user = $this->current_user->getUser();
        if (!is_null($user)) {
            $secret = $this->settings->getAppSettings()['secret'];
            $token = hash('sha512', $secret . time() . $user->id);
            $this->mapper->addToken($user->id, $token, Utility::getIP(), Utility::getAgent());
            return $token;
        }
        return null;
    }

    public function removeToken($token) {
        if (!is_null($token) && $token !== FALSE) {
            $this->mapper->deleteToken($token);
        }
    }

}
