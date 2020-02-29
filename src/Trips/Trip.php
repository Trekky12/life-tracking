<?php

namespace App\Trips;

class Trip extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_TRIPS_TRIP";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        return $this->name;
    }

}
