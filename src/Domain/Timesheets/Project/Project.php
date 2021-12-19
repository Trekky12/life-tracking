<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\Main\Utility\DateUtility;

class Project extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->is_day_based = $this->exists('is_day_based', $data) ? filter_var($data['is_day_based'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->default_view = $this->exists('default_view', $data) ? filter_var($data['default_view'], FILTER_SANITIZE_STRING) : "month";
        
        $this->has_duration_modifications = $this->exists('has_duration_modifications', $data) ? filter_var($data['has_duration_modifications'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->time_conversion_rate = $this->exists('time_conversion_rate', $data) ? filter_var($data['time_conversion_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;

        $this->default_duration = $this->exists('default_duration', $data) ? filter_var($data['default_duration'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
        
        if ($this->exists('password', $data)) {
            $this->password = filter_var($data['password'], FILTER_SANITIZE_STRING);
        }
        if ($this->exists('salt', $data)) {
            $this->salt = filter_var($data['salt'], FILTER_SANITIZE_STRING);
        }

        /**
         * Set value from request
         */
        $set_password = $this->exists('set_password', $data) ? filter_var($data['set_password'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_password)) {
            $this->password = password_hash($set_password, PASSWORD_DEFAULT);
            $this->salt = base64_encode(random_bytes(16));
        }

        $set_default_duration = $this->exists('set_default_duration', $data) ? filter_var($data['set_default_duration'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_default_duration)) {
            $this->default_duration = DateUtility::getSecondsFromDuration($set_default_duration);
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
