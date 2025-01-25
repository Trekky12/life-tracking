<?php

namespace App\Domain\Timesheets\NoticeField;

use App\Domain\Main\Utility\Utility;

class NoticeField extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_NOTICEFIELD";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
        $this->datatype = $this->exists('datatype', $data) ? Utility::filter_string_polyfill($data['datatype']) : null;
        $this->initialization = $this->exists('initialization', $data) ? trim(Utility::filter_string_polyfill($data['initialization'])) : null;
        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->type = $this->exists('type', $data) ? trim(Utility::filter_string_polyfill($data['type'])) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!in_array($this->type, array("sheet", "customer", "project"))) {
            $this->type = "sheet";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }

}
