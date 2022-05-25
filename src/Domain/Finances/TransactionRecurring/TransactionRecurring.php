<?php

namespace App\Domain\Finances\TransactionRecurring;

class TransactionRecurring extends \App\Domain\DataObject
{

    static $NAME = "DATAOBJECT_FINANCES_TRANSACTION_RECURRING";

    public function parseData(array $data)
    {
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $this->account_from = $this->exists('account_from', $data) ? filter_var($data['account_from'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->account_to = $this->exists('account_to', $data) ? filter_var($data['account_to'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;
        $this->last_run = $this->exists('last_run', $data) ? filter_var($data['last_run'], FILTER_SANITIZE_STRING) : null;
        $this->unit = $this->exists('unit', $data) ? filter_var($data['unit'], FILTER_SANITIZE_STRING) : 'month';
        $this->multiplier = $this->exists('multiplier', $data) ? filter_var($data['multiplier'], FILTER_SANITIZE_NUMBER_INT) : 1;
        $this->is_active = $this->exists('is_active', $data) ? filter_var($data['is_active'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->next_run = $this->exists('next_run', $data) ? filter_var($data['next_run'], FILTER_SANITIZE_STRING) : null;

        if (!in_array($this->unit, array_keys(self::getUnits()))) {
            $this->parsing_errors[] = "WRONG_UNIT";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings)
    {
        return $this->description;
    }

    public static function getUnits()
    {
        return array("day" => "DAY", "week" => "WEEK", "month" => "MONTH", "year" => "YEAR");
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false)
    {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["next_run"]);

        return $temp;
    }
}
