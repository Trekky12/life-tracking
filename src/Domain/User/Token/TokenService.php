<?php

namespace App\Domain\User\Token;

use App\Domain\GeneralService;
use Psr\Log\LoggerInterface;
use App\Domain\Base\Settings;
use App\Domain\Base\CurrentUser;
use App\Domain\Main\Utility\Utility;
use App\Application\Payload\Payload;

class TokenService extends GeneralService {

    private $settings;

    public function __construct(LoggerInterface $logger, CurrentUser $user, TokenMapper $mapper, Settings $settings) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
        $this->settings = $settings;
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
    
    public function index() {
        $list = $this->getTokensOfCurrentUser();
        return new Payload(Payload::$RESULT_HTML, ['list' => $list]);
    }

}
