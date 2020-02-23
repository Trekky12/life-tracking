<?php

namespace App\Car;

class Car extends \App\Base\Model {
    
    static $MODEL_NAME = "MODEL_CARS";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->mileage_per_year = $this->exists('mileage_per_year', $data) ? filter_var($data['mileage_per_year'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_term = $this->exists('mileage_term', $data) ? filter_var($data['mileage_term'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mileage_start_date = $this->exists('mileage_start_date', $data) ? filter_var($data['mileage_start_date'], FILTER_SANITIZE_STRING) : null;

        if (!is_null($this->mileage_start_date ) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->mileage_start_date)) {
            $this->mileage_start_date = date('Y-m-d');
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, array $settings) {
        return $this->name;
    }

}
