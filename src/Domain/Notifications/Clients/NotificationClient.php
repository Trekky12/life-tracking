<?php

namespace App\Domain\Notifications\Clients;

class NotificationClient extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_NOTIFICATIONS_CLIENT";

    public function parseData(array $data) {

        //$this->login = $this->exists('login', $data) ? filter_var($data['login'], FILTER_SANITIZE_STRING) : null;
        $this->endpoint = $this->exists('endpoint', $data) ? filter_var($data['endpoint'], FILTER_SANITIZE_STRING) : null;
        $this->publicKey = $this->exists('publicKey', $data) ? filter_var($data['publicKey'], FILTER_SANITIZE_STRING) : null;
        $this->authToken = $this->exists('authToken', $data) ? filter_var($data['authToken'], FILTER_SANITIZE_STRING) : null;
        $this->contentEncoding = $this->exists('contentEncoding', $data) ? filter_var($data['contentEncoding'], FILTER_SANITIZE_STRING) : null;
        $this->ip = $this->exists('ip', $data) ? filter_var($data['ip'], FILTER_SANITIZE_STRING) : null;
        $this->agent = $this->exists('agent', $data) ? filter_var($data['agent'], FILTER_SANITIZE_STRING) : null;
        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_STRING) : null;

        if ($this->exists('createdOn', $data)) {
            $this->createdOn = filter_var($data['createdOn'], FILTER_SANITIZE_STRING);
        }
    }
    
    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings): string {
        return sprintf("%s (%s)", $this->agent, $this->createdOn);
    }

}
