<?php

namespace App\Car;

class Car extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->changedOn = $this->exists('changedOn', $data) ? $data['changedOn'] : date('Y-m-d G:i:s');
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }

}
