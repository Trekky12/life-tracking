<?php

namespace App\Splitbill\Group;

class Group extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_SPLITBILLS_GROUP";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->add_finances = $this->exists('add_finances', $data) ? filter_var($data['add_finances'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->currency = $this->exists('currency', $data) ? filter_var($data['currency'], FILTER_SANITIZE_SPECIAL_CHARS) : '';

        $this->exchange_rate = $this->exists('exchange_rate', $data) ? filter_var($data['exchange_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;
        $this->exchange_fee = $this->exists('exchange_fee', $data) ? filter_var($data['exchange_fee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        return $this->name;
    }

}
