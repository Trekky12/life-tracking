<?php

namespace App\Domain\Splitbill\Group;

use App\Domain\Main\Utility\Utility;

class Group extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_SPLITBILLS_GROUP";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->add_finances = $this->exists('add_finances', $data) ? filter_var($data['add_finances'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->currency = $this->exists('currency', $data) ? filter_var($data['currency'], FILTER_SANITIZE_SPECIAL_CHARS) : '';

        $this->exchange_rate = $this->exists('exchange_rate', $data) ? filter_var($data['exchange_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;
        $this->exchange_fee = $this->exists('exchange_fee', $data) ? filter_var($data['exchange_fee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        
        $this->selected = false;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }
    
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $tmp = parent::get_fields($remove_user_element, $insert, $update);
        
        unset($tmp["selected"]);
        return $tmp;
    }

}
