<?php

namespace App\Settings;

class Setting extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_SETTING";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_STRING) : null;
        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_STRING) : "String";

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
