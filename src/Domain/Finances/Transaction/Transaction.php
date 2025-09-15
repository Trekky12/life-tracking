<?php

namespace App\Domain\Finances\Transaction;

use App\Domain\Main\Utility\Utility;

class Transaction extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_TRANSACTION";

    public function parseData(array $data) {

        $this->date = $this->exists('date', $data) ? Utility::filter_string_polyfill($data['date']) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? Utility::filter_string_polyfill($data['time']) : date('H:i:s');

        $this->description = $this->exists('description', $data) ? Utility::filter_string_polyfill($data['description']) : null;
        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;

        $this->account_from = $this->exists('account_from', $data) ? filter_var($data['account_from'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->account_to = $this->exists('account_to', $data) ? filter_var($data['account_to'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->finance_entry = $this->exists('finance_entry', $data) ? filter_var($data['finance_entry'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->bill_entry = $this->exists('bill_entry', $data) ? filter_var($data['bill_entry'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->is_confirmed = $this->exists('is_confirmed', $data) ? filter_var($data['is_confirmed'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->is_round_up_savings = $this->exists('is_round_up_savings', $data) ? filter_var($data['is_round_up_savings'], FILTER_SANITIZE_NUMBER_INT) : 0;
    
        $this->is_exchange_fee = $this->exists('is_exchange_fee', $data) ? filter_var($data['is_exchange_fee'], FILTER_SANITIZE_NUMBER_INT) : 0;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->description;
    }

}
