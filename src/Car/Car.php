<?php

namespace App\Car;

class Car extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        
        $this->mileage_year = $this->exists('mileage_year', $data) ? filter_var($data['mileage_year'], FILTER_SANITIZE_NUMBER_INT) : null;

        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }

}
