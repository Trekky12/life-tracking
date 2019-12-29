<?php

namespace App\Notifications\Categories;

class Category extends \App\Base\Model {
    
    static $MODEL_NAME = "MODEL_NOTIFICATIONS_CATEGORY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->identifier = $this->exists('identifier', $data) ? filter_var($data['identifier'], FILTER_SANITIZE_STRING) : null;
        $this->internal = $this->exists('internal', $data) ? intval(filter_var($data['internal'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (empty($this->identifier)) {
            $this->parsing_errors[] = "IDENTIFIER_CANNOT_BE_EMPTY";
        }
    }

    public function isInternal(){
        return $this->internal == 1;
    }

    public function getDescription(\Interop\Container\ContainerInterface $ci) {
        return $this->name;
    }

}
