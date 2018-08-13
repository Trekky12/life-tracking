<?php

namespace App\Finances;

class FinancesEntry extends \App\Base\Model {

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : date('H:i:s');
        // Default category 1 => not categorized, otherwise the statistics is not that easy
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? trim(filter_var($data['description'], FILTER_SANITIZE_STRING)) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? trim(filter_var($data['notice'], FILTER_SANITIZE_STRING)) : null;


        $set_common = $this->exists('set_common', $data) ? filter_var($data['set_common'], FILTER_SANITIZE_STRING) : 0;
        $this->common = $set_common === 'on' ? 1 : 0;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : $this->common;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        
        $this->fixed = $this->exists('fixed', $data) ? filter_var($data['fixed'], FILTER_SANITIZE_NUMBER_INT) : 0;

        /**
         * Clean date/time
         */
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        /**
         * Parsing Errors
         */
        if (!in_array($this->type, array(0, 1))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }
        if (is_null($this->value)) {
            $this->parsing_errors[] = "VALUE_CANNOT_BE_EMPTY";
        }
        if (is_null($this->description)) {
            $this->parsing_errors[] = "DESCRIPTION_CANNOT_BE_EMPTY";
        }
    }

}
