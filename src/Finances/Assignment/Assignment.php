<?php

namespace App\Finances\Assignment;

class Assignment extends \App\Base\Model {
    
    static $MODEL_NAME = "MODEL_FINANCES_ASSIGNMENT_ENTRY";

    public function parseData(array $data) {

        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->min_value = $this->exists('min_value', $data) ? filter_var($data['min_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->max_value = $this->exists('max_value', $data) ? filter_var($data['max_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        if (is_null($this->description)) {
            $this->parsing_errors[] = "DESCRIPTION_CANNOT_BE_EMPTY";
        }
        if (is_null($this->category)) {
            $this->parsing_errors[] = "CATEGORY_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, array $settings) {
        return $this->description;
    }

}
