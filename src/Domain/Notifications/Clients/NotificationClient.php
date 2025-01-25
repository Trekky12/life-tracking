<?php

namespace App\Domain\Notifications\Clients;

use App\Domain\Main\Utility\Utility;

class NotificationClient extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_NOTIFICATIONS_CLIENT";

    public function parseData(array $data) {

        //$this->login = $this->exists('login', $data) ? Utility::filter_string_polyfill($data['login']) : null;
        $this->endpoint = $this->exists('endpoint', $data) ? Utility::filter_string_polyfill($data['endpoint']) : null;
        $this->publicKey = $this->exists('publicKey', $data) ? Utility::filter_string_polyfill($data['publicKey']) : null;
        $this->authToken = $this->exists('authToken', $data) ? Utility::filter_string_polyfill($data['authToken']) : null;
        $this->contentEncoding = $this->exists('contentEncoding', $data) ? Utility::filter_string_polyfill($data['contentEncoding']) : null;
        $this->ip = $this->exists('ip', $data) ? Utility::filter_string_polyfill($data['ip']) : null;
        $this->agent = $this->exists('agent', $data) ? Utility::filter_string_polyfill($data['agent']) : null;
        $this->type = $this->exists('type', $data) ? Utility::filter_string_polyfill($data['type']) : null;

        if ($this->exists('createdOn', $data)) {
            $this->createdOn = Utility::filter_string_polyfill($data['createdOn']);
        }
    }
    
    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings): string {
        return sprintf("%s (%s)", $this->agent, $this->createdOn);
    }

}
