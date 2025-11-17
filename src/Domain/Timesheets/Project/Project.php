<?php

namespace App\Domain\Timesheets\Project;

use App\Domain\Main\Utility\DateUtility;
use App\Domain\Main\Utility\Utility;

class Project extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_UNSAFE_RAW) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->is_day_based = $this->exists('is_day_based', $data) ? filter_var($data['is_day_based'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->default_view = $this->exists('default_view', $data) ? filter_var($data['default_view'], FILTER_UNSAFE_RAW) : "month";

        $this->has_duration_modifications = $this->exists('has_duration_modifications', $data) ? filter_var($data['has_duration_modifications'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->time_conversion_rate = $this->exists('time_conversion_rate', $data) ? filter_var($data['time_conversion_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;

        $this->default_duration = $this->exists('default_duration', $data) ? filter_var($data['default_duration'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->show_week_button = $this->exists('show_week_button', $data) ? intval(filter_var($data['show_week_button'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        $this->show_month_button = $this->exists('show_month_button', $data) ? intval(filter_var($data['show_month_button'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        $this->show_quarters_buttons = $this->exists('show_quarters_buttons', $data) ? intval(filter_var($data['show_quarters_buttons'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->customers_name_singular = $this->exists('customers_name_singular', $data) ? filter_var($data['customers_name_singular'], FILTER_UNSAFE_RAW) : null;
        $this->customers_name_plural = $this->exists('customers_name_plural', $data) ? filter_var($data['customers_name_plural'], FILTER_UNSAFE_RAW) : null;

        $this->slot_min_time = $this->exists('slot_min_time', $data) ? filter_var($data['slot_min_time'], FILTER_SANITIZE_SPECIAL_CHARS) : "00:00:00";
        $this->slot_max_time = $this->exists('slot_max_time', $data) ? filter_var($data['slot_max_time'], FILTER_SANITIZE_SPECIAL_CHARS) : "24:00:00";

        $this->repeat_count = $this->exists('repeat_count', $data) ? filter_var($data['repeat_count'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $this->repeat_unit = $this->exists('repeat_unit', $data) ? Utility::filter_string_polyfill($data['repeat_unit']) : 'month';
        $this->repeat_multiplier = $this->exists('repeat_multiplier', $data) ? filter_var($data['repeat_multiplier'], FILTER_SANITIZE_NUMBER_INT) : 1;

        $this->hide_monday = $this->exists('hide_monday', $data) ? filter_var($data['hide_monday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_tuesday = $this->exists('hide_tuesday', $data) ? filter_var($data['hide_tuesday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_wednesday = $this->exists('hide_wednesday', $data) ? filter_var($data['hide_wednesday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_thursday = $this->exists('hide_thursday', $data) ? filter_var($data['hide_thursday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_friday = $this->exists('hide_friday', $data) ? filter_var($data['hide_friday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_saturday = $this->exists('hide_saturday', $data) ? filter_var($data['hide_saturday'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->hide_sunday = $this->exists('hide_sunday', $data) ? filter_var($data['hide_sunday'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->report_headline = $this->exists('report_headline', $data) ? filter_var($data['report_headline'], FILTER_UNSAFE_RAW) : null;

        $this->has_billing = $this->exists('has_billing', $data) ? filter_var($data['has_billing'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->has_end = $this->exists('has_end', $data) ? filter_var($data['has_end'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->has_location = $this->exists('has_location', $data) ? intval(filter_var($data['has_location'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->create_only_notices = $this->exists('create_only_notices', $data) ? intval(filter_var($data['create_only_notices'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        
        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }


        /**
         * Set value from request
         */

        $set_default_duration = $this->exists('set_default_duration', $data) ? filter_var($data['set_default_duration'], FILTER_UNSAFE_RAW) : null;
        if (!is_null($set_default_duration)) {
            $this->default_duration = DateUtility::getSecondsFromDuration($set_default_duration);
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public static function getUnits() {
        return array("day" => "DAY", "week" => "WEEK", "month" => "MONTH", "year" => "YEAR");
    }

    public function getHiddenDays() {
        $hidden_days = [];

        if ($this->hide_monday) {
            $hidden_days[] = 1;
        }
        if ($this->hide_tuesday) {
            $hidden_days[] = 2;
        }
        if ($this->hide_wednesday) {
            $hidden_days[] = 3;
        }
        if ($this->hide_thursday) {
            $hidden_days[] = 4;
        }
        if ($this->hide_friday) {
            $hidden_days[] = 5;
        }
        if ($this->hide_saturday) {
            $hidden_days[] = 6;
        }
        if ($this->hide_sunday) {
            $hidden_days[] = 0;
        }

        return $hidden_days;
    }
}
