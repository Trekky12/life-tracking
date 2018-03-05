<?php

namespace App\Board;

class Card extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');
        $this->title = $this->exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;

        $this->stack = $this->exists('stack', $data) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;


        if (empty($this->title)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}