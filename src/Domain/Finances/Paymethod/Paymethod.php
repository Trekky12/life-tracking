<?php

namespace App\Domain\Finances\Paymethod;

use App\Domain\Main\Utility\Utility;

class Paymethod extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_FINANCES_PAYMETHOD";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->account = $this->exists('account', $data) ? filter_var($data['account'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->round_up_savings = $this->exists('round_up_savings', $data) ? intval(filter_var($data['round_up_savings'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        $this->round_up_savings_account = $this->exists('round_up_savings_account', $data) ? filter_var($data['round_up_savings_account'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (is_null($this->account)) {
            $this->parsing_errors[] = "ACCOUNT_REQUIRED";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }
}
