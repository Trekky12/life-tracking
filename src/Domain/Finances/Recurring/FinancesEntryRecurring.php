<?php

namespace App\Domain\Finances\Recurring;

use App\Domain\Main\Utility\Utility;

class FinancesEntryRecurring extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_ENTRY_RECURRING";

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? Utility::filter_string_polyfill($data['description']) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->paymethod = $this->exists('paymethod', $data) ? filter_var($data['paymethod'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;
        $this->last_run = $this->exists('last_run', $data) ? Utility::filter_string_polyfill($data['last_run']) : null;
        $this->unit = $this->exists('unit', $data) ? Utility::filter_string_polyfill($data['unit']) : 'month';
        $this->multiplier = $this->exists('multiplier', $data) ? filter_var($data['multiplier'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $this->is_active = $this->exists('is_active', $data) ? filter_var($data['is_active'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->next_run = $this->exists('next_run', $data) ? Utility::filter_string_polyfill($data['next_run']) : null;

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
        if (!in_array($this->unit, array_keys(self::getUnits()))) {
            $this->parsing_errors[] = "WRONG_UNIT";
        }
    }

    public static function getUnits() {
        return array("day" => "DAY", "week" => "WEEK", "month" => "MONTH", "year" => "YEAR");
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        $currency = $settings->getAppSettings()['i18n']['currency'];
        return sprintf("%s (%s %s)", $this->description, $this->value, $currency);
    }
    
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["next_run"]);

        return $temp;
    }

}
