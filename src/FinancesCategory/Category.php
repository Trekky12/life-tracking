<?php

namespace App\FinancesCategory;

class Category extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }

}
