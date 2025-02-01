<?php

namespace App\Domain\Activity;

use App\Domain\Main\Utility\Utility;

class Activity extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_ACTIVITY";

    public function parseData(array $data) {

        $this->type = $this->exists('type', $data) ? Utility::filter_string_polyfill($data['type']) : null;
        $this->module = $this->exists('module', $data) ? Utility::filter_string_polyfill($data['module']) : null;

        $this->object = $this->exists('object', $data) ? Utility::filter_string_polyfill($data['object']) : null;
        $this->object_id = $this->exists('object_id', $data) ? intval(filter_var($data['object_id'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $this->object_description = $this->exists('object_description', $data) ? Utility::filter_string_polyfill($data['object_description']) : null;

        $this->parent_object = $this->exists('parent_object', $data) ? Utility::filter_string_polyfill($data['parent_object']) : null;
        $this->parent_object_id = $this->exists('parent_object_id', $data) ? intval(filter_var($data['parent_object_id'], FILTER_SANITIZE_NUMBER_INT)) : null;
        $this->parent_object_description = $this->exists('parent_object_description', $data) ? Utility::filter_string_polyfill($data['parent_object_description']) : null;

        $this->link = $this->exists('link', $data) ? Utility::filter_string_polyfill($data['link']) : null;

        $this->additional_information = $this->exists('additional_information', $data) ? Utility::filter_string_polyfill($data['additional_information']) : null;
    }
}
