<?php

namespace App\Notifications;

class Notification extends \App\Base\Model {

    public function parseData(array $data) {

        $this->client = $this->exists('client', $data) ? filter_var($data['client'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;        
        $this->title = $this->exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;
        $this->message = $this->exists('message', $data) ? filter_var($data['message'], FILTER_SANITIZE_STRING) : null;
        $this->seen = $this->exists('seen', $data) ? filter_var($data['seen'], FILTER_SANITIZE_STRING) : null;
        
        /**
         * Value from DB
         */
        if ($this->exists('createdOn', $data)) {
            $this->createdOn = filter_var($data['createdOn'], FILTER_SANITIZE_STRING);
        }
    }

}
