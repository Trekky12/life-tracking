<?php

namespace App\Domain\Timesheets\Project;

class Project extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->is_day_based = $this->exists('is_day_based', $data) ? filter_var($data['is_day_based'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->default_view = $this->exists('default_view', $data) ? filter_var($data['default_view'], FILTER_SANITIZE_STRING) : "month";
        
        $this->has_time_conversion = $this->exists('has_time_conversion', $data) ? filter_var($data['has_time_conversion'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->time_conversion_rate = $this->exists('time_conversion_rate', $data) ? filter_var($data['time_conversion_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
