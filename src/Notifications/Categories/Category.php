<?php

namespace App\Notifications\Categories;

class Category extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->identifier = $this->exists('identifier', $data) ? filter_var($data['identifier'], FILTER_SANITIZE_STRING) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
        
        if (empty($this->identifier)) {
            $this->parsing_errors[] = "IDENTIFIER_CANNOT_BE_EMPTY";
        }
    }

}
