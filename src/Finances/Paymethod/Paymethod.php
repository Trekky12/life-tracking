<?php

namespace App\Finances\Paymethod;

class Paymethod extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name       = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }

}
