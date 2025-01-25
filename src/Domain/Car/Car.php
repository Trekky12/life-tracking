<?php

namespace App\Domain\Car;

use App\Domain\Main\Utility\Utility;

class Car extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_CARS";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;

        $this->mileage_per_year = $this->exists('mileage_per_year', $data) ? filter_var($data['mileage_per_year'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_term = $this->exists('mileage_term', $data) ? filter_var($data['mileage_term'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_start_date = $this->exists('mileage_start_date', $data) ? Utility::filter_string_polyfill($data['mileage_start_date']) : null;

        $this->mileage_start = $this->exists('mileage_start', $data) ? filter_var($data['mileage_start'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        if (!is_null($this->mileage_start_date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->mileage_start_date)) {
            $this->mileage_start_date = date('Y-m-d');
        }
        
        if (!is_null($this->mileage_start_date) && is_null($this->mileage_start)) {
            $this->mileage_start = 0;
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
