<?php

namespace App\Domain\Trips;

class Trip extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TRIPS_TRIP";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
