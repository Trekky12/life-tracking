<?php

namespace App\Domain\Timesheets\Project;

class Project extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->is_day_based = $this->exists('is_day_based', $data) ? filter_var($data['is_day_based'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->default_view = $this->exists('default_view', $data) ? filter_var($data['default_view'], FILTER_SANITIZE_STRING) : "month";

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
