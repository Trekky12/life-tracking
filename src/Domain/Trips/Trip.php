<?php

namespace App\Domain\Trips;

use App\Domain\Main\Utility\Utility;

class Trip extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TRIPS_TRIP";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        $this->min_date = $this->exists('min_date', $data) ? filter_var($data['min_date'], FILTER_SANITIZE_STRING) : null;

        $this->max_date = $this->exists('max_date', $data) ? filter_var($data['max_date'], FILTER_SANITIZE_STRING) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getNotice() {
        return Utility::replaceLinks($this->notice);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $tmp = parent::get_fields($remove_user_element, $insert, $update);

        unset($tmp["min_date"]);
        unset($tmp["max_date"]);

        return $tmp;
    }

}
