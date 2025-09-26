<?php

namespace App\Domain\Settings;

use App\Domain\Service;
use Psr\Log\LoggerInterface;
use App\Domain\Base\CurrentUser;
use App\Application\Payload\Payload;

class SettingsService extends Service {

    protected $mapper;

    public function __construct(
        LoggerInterface $logger,
        CurrentUser $user,
        SettingsMapper $mapper
    ) {
        parent::__construct($logger, $user);
        $this->mapper = $mapper;
    }

    public function index() {
        $cachemiss = $this->mapper->getSetting("cachemiss");

        return new Payload(Payload::$RESULT_HTML, [
            "cachemiss" => $cachemiss
        ]);
    }

    public function save($data) {
        $cachemiss = array_key_exists("cachemiss", $data) ? filter_var($data["cachemiss"]) : null;

        if(!is_null($cachemiss)){
            $this->mapper->addOrUpdateSetting("cachemiss", $cachemiss, "String");
        }
        return new Payload(Payload::$STATUS_UPDATE, $data);
    }
}
