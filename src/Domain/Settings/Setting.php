<?php

namespace App\Domain\Settings;

use App\Domain\Main\Utility\Utility;

class Setting extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_SETTING";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->value = $this->exists('value', $data) ? Utility::filter_string_polyfill($data['value']) : null;
        $this->type = $this->exists('type', $data) ? Utility::filter_string_polyfill($data['type']) : "String";
        $this->reference = $this->exists('reference', $data) ? intval(filter_var($data['reference'], FILTER_SANITIZE_NUMBER_INT)) : null;

        if (!in_array($this->type, array("String", "Integer", "Boolean", "Date"))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }

        if (is_null($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getValue() {
        switch ($this->type) {
            case "String":
                return $this->value;

            case "Integer":
                return intval($this->value);

            case "Boolean":
                return boolval($this->value);

            case "Date":
                $date = new \DateTime();
                $date->setTimestamp(intval($this->value));
                return $date;
        }

        return $this->value;
    }

    public function getDayDiff() {
        if ($this->type !== "Date") {
            return null;
        }
        $current_time = new \DateTime();
        $value = $this->getValue();

        // don't calculate 24 hour difference but absolute days 
        // so the time is not interesing
        $current_time->setTime(0, 0, 0);
        $value->setTime(0, 0, 0);

        $diff = $current_time->diff($value);
        return $diff->days;
    }

}
