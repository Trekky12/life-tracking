<?php

namespace App\Crawler;

class Crawler extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
