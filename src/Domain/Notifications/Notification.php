<?php

namespace App\Domain\Notifications;

use App\Domain\Main\Utility\Utility;

class Notification extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_NOTIFICATIONS_NOTIFICATION";

    public function parseData(array $data) {

        $this->user = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->title = $this->exists('title', $data) ? Utility::filter_string_polyfill($data['title']) : null;
        $this->message = $this->exists('message', $data) ? Utility::filter_string_polyfill($data['message']) : null;
        $this->seen = $this->exists('seen', $data) ? Utility::filter_string_polyfill($data['seen']) : null;
        $this->link = $this->exists('link', $data) ? Utility::filter_string_polyfill($data['link']) : null;

        /**
         * Value from DB
         */
        if ($this->exists('createdOn', $data)) {
            $this->createdOn = Utility::filter_string_polyfill($data['createdOn']);
        }
    }

}
