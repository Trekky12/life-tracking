<?php

namespace App\Domain\Notifications\Categories;

use App\Domain\Main\Utility\Utility;

class Category extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_NOTIFICATIONS_CATEGORY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->identifier = $this->exists('identifier', $data) ? Utility::filter_string_polyfill($data['identifier']) : null;
        $this->internal = $this->exists('internal', $data) ? intval(filter_var($data['internal'], FILTER_SANITIZE_NUMBER_INT)) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (empty($this->identifier)) {
            $this->parsing_errors[] = "IDENTIFIER_CANNOT_BE_EMPTY";
        }
    }

    public function isInternal() {
        return $this->internal == 1;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
