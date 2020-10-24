<?php

namespace App\Domain\Workouts\Plan;

class Plan extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_PLAN";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $this->is_template = $this->exists('is_template', $data) ? intval(filter_var($data['is_template'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
