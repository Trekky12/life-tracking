<?php

namespace App\Splitbill\Group;

class Group extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        
        $this->add_finances = $this->exists('add_finances', $data) ? filter_var($data['add_finances'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->currency = $this->exists('currency', $data) ? filter_var($data['currency'], FILTER_SANITIZE_SPECIAL_CHARS) : '';

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
