<?php

namespace App\Crawler;

class Crawler extends \App\Base\DataObject {

    static $NAME = "DATAOBJECT_CRAWLERS_CRAWLER";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : null;
        $this->filter = $this->exists('filter', $data) ? filter_var($data['filter'], FILTER_SANITIZE_STRING) : "createdOn";

        if (!in_array($this->filter, array("changedOn", "createdOn"))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        return $this->name;
    }

}
