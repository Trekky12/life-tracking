<?php

namespace App\Domain\Splitbill\Bill;

use App\Domain\Main\Utility\Utility;

class Bill extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_SPLITBILLS_BILL";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->sbgroup = $this->exists('sbgroup', $data) ? filter_var($data['sbgroup'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->date = $this->exists('date', $data) ? Utility::filter_string_polyfill($data['date']) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? Utility::filter_string_polyfill($data['time']) : date('H:i:s');

        $this->lat = $this->exists('lat', $data) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->lng = $this->exists('lng', $data) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->acc = $this->exists('acc', $data) ? filter_var($data['acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;

        $this->settleup = $this->exists('settleup', $data) ? intval(filter_var($data['settleup'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->exchange_rate = $this->exists('exchange_rate', $data) ? filter_var($data['exchange_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;
        $this->exchange_fee = $this->exists('exchange_fee', $data) ? filter_var($data['exchange_fee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        /**
         * Clean date/time
         */
        if (!is_null($this->date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!is_null($this->time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        if (empty($this->name) && $this->settleup == 0) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }


        $this->spend = $this->exists('spend', $data) ? filter_var($data['spend'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->paid = $this->exists('paid', $data) ? filter_var($data['paid'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->balance = $this->exists('balance', $data) ? filter_var($data['balance'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $this->spend_foreign = $this->exists('spend_foreign', $data) ? filter_var($data['spend_foreign'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->paid_foreign = $this->exists('paid_foreign', $data) ? filter_var($data['paid_foreign'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->paid_by = $this->exists('paid_by', $data) ? Utility::filter_string_polyfill($data['paid_by']) : null;
        $this->spend_by = $this->exists('spend_by', $data) ? Utility::filter_string_polyfill($data['spend_by']) : null;
    }

    /**
     * Remove fields which are not in the db table
     */
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["spend"]);
        unset($temp["paid"]);
        unset($temp["balance"]);

        unset($temp["spend_foreign"]);
        unset($temp["paid_foreign"]);

        return $temp;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->sbgroup;
    }

}
