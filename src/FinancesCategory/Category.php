<?php

namespace App\FinancesCategory;

class Category extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name       = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_STRING) : 0;
        
        $set_default = $this->exists('set_default', $data) ? filter_var($data['set_default'], FILTER_SANITIZE_STRING) : 0;
        $this->is_default = $set_default === 'on' ? 1 : 0;
        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_NUMBER_INT) : $this->is_default;


        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }

}
