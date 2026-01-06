<?php

namespace App\Domain\Notifications\Categories;

use App\Domain\Main\Utility\Utility;

class Category extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_NOTIFICATIONS_CATEGORY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->identifier = $this->exists('identifier', $data) ? Utility::filter_string_polyfill($data['identifier']) : null;
        $this->internal = $this->exists('internal', $data) ? intval(filter_var($data['internal'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        $this->reminder = $this->exists('reminder', $data) ? intval(filter_var($data['reminder'], FILTER_SANITIZE_NUMBER_INT)) : null;

        if (is_null($this->reminder) && empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (is_null($this->reminder) && empty($this->identifier)) {
            $this->parsing_errors[] = "IDENTIFIER_CANNOT_BE_EMPTY";
        }
    }

    public function isInternal() {
        return $this->internal == 1;
    }

    public function hasReminder() {
        return !is_null($this->reminder);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

}
