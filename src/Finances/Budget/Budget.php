<?php

namespace App\Finances\Budget;

class Budget extends \App\Base\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_BUDGET_ENTRY";

    public function parseData(array $data) {

        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;

        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->sum = $this->exists('sum', $data) ? filter_var($data['sum'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->percent = $this->exists('percent', $data) ? filter_var($data['percent'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->diff = $this->exists('diff', $data) ? filter_var($data['diff'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $this->is_hidden = $this->exists('is_hidden', $data) ? filter_var($data['is_hidden'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->is_remaining = $this->exists('is_remaining', $data) ? filter_var($data['is_remaining'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (is_null($this->description)) {
            $this->parsing_errors[] = "DESCRIPTION_CANNOT_BE_EMPTY";
        }
    }

    public function is_remaining() {
        return intval($this->is_remaining) == 0 ? false : true;
    }

    /**
     * Remove fields which are not in the db table
     */
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["sum"]);
        unset($temp["percent"]);
        unset($temp["diff"]);

        return $temp;
    }

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        $currency = $settings->getAppSettings()['i18n']['currency'];
        return sprintf("%s (%s %s)", $this->description, $this->value, $currency);
    }

}
