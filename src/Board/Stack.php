<?php

namespace App\Board;

class Stack extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->board = $this->exists('board', $data) ? filter_var($data['board'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
