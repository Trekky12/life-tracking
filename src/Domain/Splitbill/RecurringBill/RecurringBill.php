<?php

namespace App\Domain\Splitbill\RecurringBill;

use App\Domain\Main\Utility\Utility;

class RecurringBill extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_SPLITBILLS_BILL_RECURRING";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? trim(Utility::filter_string_polyfill($data['name'])) : null;
        $this->sbgroup = $this->exists('sbgroup', $data) ? filter_var($data['sbgroup'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->notice = $this->exists('notice', $data) ? trim(Utility::filter_string_polyfill($data['notice'])) : null;

        $this->settleup = $this->exists('settleup', $data) ? intval(filter_var($data['settleup'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        $this->exchange_rate = $this->exists('exchange_rate', $data) ? filter_var($data['exchange_rate'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 1;
        $this->exchange_fee = $this->exists('exchange_fee', $data) ? filter_var($data['exchange_fee'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;
        $this->last_run = $this->exists('last_run', $data) ? Utility::filter_string_polyfill($data['last_run']) : null;
        $this->unit = $this->exists('unit', $data) ? Utility::filter_string_polyfill($data['unit']) : 'month';
        $this->multiplier = $this->exists('multiplier', $data) ? filter_var($data['multiplier'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $this->is_active = $this->exists('is_active', $data) ? filter_var($data['is_active'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->next_run = $this->exists('next_run', $data) ? Utility::filter_string_polyfill($data['next_run']) : null;

        if (!in_array($this->unit, array_keys(self::getUnits()))) {
            $this->parsing_errors[] = "WRONG_UNIT";
        }
        if (empty($this->name)) {
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

        unset($temp["next_run"]);

        return $temp;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->sbgroup;
    }

    public static function getUnits() {
        return array("day" => "DAY", "week" => "WEEK", "month" => "MONTH", "year" => "YEAR");
    }

}
