<?php

namespace App\Board;

class Board extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        if(empty($this->name)){
            $this->parsing_errors[] ="NAME_CANNOT_BE_EMPTY";
        }
    }
}
