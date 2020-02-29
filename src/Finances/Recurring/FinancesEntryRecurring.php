<?php

namespace App\Finances\Recurring;

class FinancesEntryRecurring extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_FINANCES_ENTRY_RECURRING";

    public function parseData(array $data) {

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        $this->last_run = $this->exists('last_run', $data) ? filter_var($data['last_run'], FILTER_SANITIZE_STRING) : null;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->unit = $this->exists('unit', $data) ? filter_var($data['unit'], FILTER_SANITIZE_STRING) : 'month';
        $this->multiplier = $this->exists('multiplier', $data) ? filter_var($data['multiplier'], FILTER_SANITIZE_NUMBER_INT) : 1;

        $this->paymethod = $this->exists('paymethod', $data) ? filter_var($data['paymethod'], FILTER_SANITIZE_NUMBER_INT) : null;

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

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        $currency = $settings->getAppSettings()['i18n']['currency'];
        return sprintf("%s (%s %s)", $this->description, $this->value, $currency);
    }

}
