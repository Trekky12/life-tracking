<?php

namespace App\Domain\Finances;

class FinancesEntry extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_ENTRY";

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : date('H:i:s');
        // Default category 1 => not categorized, otherwise the statistics is not that easy
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? trim(filter_var($data['description'], FILTER_SANITIZE_STRING)) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? trim(filter_var($data['notice'], FILTER_SANITIZE_STRING)) : null;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        $this->fixed = $this->exists('fixed', $data) ? filter_var($data['fixed'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->lat = $this->exists('lat', $data) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->lng = $this->exists('lng', $data) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->acc = $this->exists('acc', $data) ? filter_var($data['acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->bill = $this->exists('bill', $data) ? filter_var($data['bill'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->paymethod = $this->exists('paymethod', $data) ? filter_var($data['paymethod'], FILTER_SANITIZE_NUMBER_INT) : null;


        if (is_null($this->bill)) {
            /**
             * Clean date/time
             */
            if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
                $this->date = date('Y-m-d');
            }

            if (!preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
                $this->time = date('H:i:s');
            }

            /**
             * Parsing Errors
             */
            if (!in_array($this->type, array(0, 1))) {
                $this->parsing_errors[] = "WRONG_TYPE";
            }
            if (is_null($this->value)) {
                $this->parsing_errors[] = "VALUE_CANNOT_BE_EMPTY";
            }
        }
        if (is_null($this->description)) {
            $this->parsing_errors[] = "DESCRIPTION_CANNOT_BE_EMPTY";
        }
    }

    public function getPosition() {
        return [
            'id' => $this->id,
            'dt' => $this->date . ' ' . $this->time,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'acc' => $this->acc,
            'description' => $this->description,
            'value' => $this->value,
            'type' => 1];
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {

        if (!is_null($this->bill) && !$insert) {
            /**
             * When a finance entry from a bill is edited, 
             * only the following fields can be updated
             */
            $temp = [];
            $temp["id"] = $this->id;
            $temp["category"] = $this->category;
            $temp["description"] = $this->description;
            $temp["changedOn"] = $this->changedOn;
            $temp["paymethod"] = $this->paymethod;
            $temp["notice"] = $this->notice;
            if (!$remove_user_element) {
                $temp["user"] = $this->user;
            }
            return $temp;
        }

        return parent::get_fields($remove_user_element, $insert, $update);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        $currency = $settings->getAppSettings()['i18n']['currency'];
        return sprintf("%s (%s %s)", $this->description, $this->value, $currency);
    }

}
