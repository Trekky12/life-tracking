<?php

namespace App\Domain\Car;

use App\Domain\Main\Utility\Utility;

class Car extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_CARS";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->mileage_per_year = $this->exists('mileage_per_year', $data) ? filter_var($data['mileage_per_year'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_term = $this->exists('mileage_term', $data) ? filter_var($data['mileage_term'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_start_date = $this->exists('mileage_start_date', $data) ? Utility::filter_string_polyfill($data['mileage_start_date']) : null;

        $this->mileage_start = $this->exists('mileage_start', $data) ? filter_var($data['mileage_start'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $this->refill_type = $this->exists('refill_type', $data) ? Utility::filter_string_polyfill($data['refill_type']) : null;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (!is_null($this->mileage_start_date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->mileage_start_date)) {
            $this->mileage_start_date = date('Y-m-d');
        }
        
        if (!is_null($this->mileage_start_date) && is_null($this->mileage_start)) {
            $this->mileage_start = 0;
        }

        if (!in_array($this->refill_type, ["fuel", "battery"])) {
            $this->refill_type = "fuel";
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
        
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
