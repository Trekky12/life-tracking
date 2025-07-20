<?php

namespace App\Domain\Crawler;

use App\Domain\Main\Utility\Utility;

class Crawler extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_CRAWLERS_CRAWLER";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $this->filter = $this->exists('filter', $data) ? Utility::filter_string_polyfill($data['filter']) : "createdOn";
        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (!in_array($this->filter, array("changedOn", "createdOn"))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }
}
