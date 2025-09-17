<?php

namespace App\Domain\Finances;

use App\Domain\Main\Utility\Utility;

class FinancesEntry extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_ENTRY";

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->date = $this->exists('date', $data) ? Utility::filter_string_polyfill($data['date']) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? Utility::filter_string_polyfill($data['time']) : date('H:i:s');
        // Default category 1 => not categorized, otherwise the statistics is not that easy
        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->description = $this->exists('description', $data) ? Utility::filter_string_polyfill($data['description']) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;

        $this->common = $this->exists('common', $data) ? filter_var($data['common'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->common_value = $this->exists('common_value', $data) ? filter_var($data['common_value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        $this->fixed = $this->exists('fixed', $data) ? filter_var($data['fixed'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->lat = $this->exists('lat', $data) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->lng = $this->exists('lng', $data) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->acc = $this->exists('acc', $data) ? filter_var($data['acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->bill = $this->exists('bill', $data) ? filter_var($data['bill'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->bill_paid = $this->exists('bill_paid', $data) ? filter_var($data['bill_paid'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->bill_paid_foreign = $this->exists('bill_paid_foreign', $data) ? filter_var($data['bill_paid_foreign'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        $this->paymethod = $this->exists('paymethod', $data) ? filter_var($data['paymethod'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->transaction = $this->exists('transaction', $data) ? filter_var($data['transaction'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->transaction_round_up_savings = $this->exists('transaction_round_up_savings', $data) ? filter_var($data['transaction_round_up_savings'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->transaction_exchange_fee = $this->exists('transaction_exchange_fee', $data) ? filter_var($data['transaction_exchange_fee'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (is_null($this->bill)) {
            /**
             * Clean date/time
             */
            if (!is_null($this->date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
                $this->date = date('Y-m-d');
            }

            if (!is_null($this->time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
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
            'type' => 1
        ];
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {

        $is_bill_based_save = !is_null($this->bill) && (is_array($this->additionalData) && array_key_exists("is_bill_based_save", $this->additionalData) && $this->additionalData["is_bill_based_save"]);

        /**
         * When a finance entry from a bill is edited, 
         * only the following fields can be updated
         */
        if (!is_null($this->bill) && !$is_bill_based_save) {
            $temp = [];
            $temp["id"] = $this->id;
            $temp["category"] = $this->category;
            $temp["description"] = $this->description;
            $temp["changedOn"] = $this->changedOn;
            $temp["notice"] = $this->notice;
            if (!$remove_user_element) {
                $temp["user"] = $this->user;
            }
            return $temp;
        }

        $temp = parent::get_fields($remove_user_element, $insert, $update);
        unset($temp["transaction"]);
        unset($temp["transaction_round_up_savings"]);
        unset($temp["transaction_exchange_fee"]);

        return $temp;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        $currency = $settings->getAppSettings()['i18n']['currency'];
        return sprintf("%s (%s %s)", $this->description, $this->value, $currency);
    }
}
