<?php

namespace App\Domain\Timesheets\CustomerRequirement;

use App\Domain\Main\Utility\Utility;

class CustomerRequirement extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_CUSTOMER_REQUIREMENT";

    public function parseData(array $data) {

        $this->requirement_type = $this->exists('requirement_type', $data) ? filter_var($data['requirement_type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->customer = $this->exists('customer', $data) ? filter_var($data['customer'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->value = $this->exists('value', $data) ? trim(Utility::filter_string_polyfill($data['value'])) : null;
        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;
        $this->is_valid = $this->exists('is_valid', $data) ? boolval(Utility::filter_string_polyfill($data['is_valid'])) : false;
    }

    public function getParentID() {
        return $this->requirement_type;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["is_valid"]);

        return $temp;
    }
}
