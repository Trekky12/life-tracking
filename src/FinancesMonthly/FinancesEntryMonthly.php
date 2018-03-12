<?php

namespace App\FinancesMonthly;

class FinancesEntryMonthly extends \App\Base\Model {

    public function parseData(array $data) {

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        $this->last_run = $this->exists('last_run', $data) ? filter_var($data['last_run'], FILTER_SANITIZE_STRING) : null;

        $set_common = $this->exists('set_common', $data) ? filter_var($data['set_common'], FILTER_SANITIZE_STRING) : 0;
        $this->common = $set_common === 'on' ? 1 : 0;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : $this->common;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


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
