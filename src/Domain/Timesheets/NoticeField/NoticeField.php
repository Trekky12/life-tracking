<?php

namespace App\Domain\Timesheets\NoticeField;

class NoticeField extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_NOTICEFIELD";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
        $this->datatype = $this->exists('datatype', $data) ? filter_var($data['datatype'], FILTER_SANITIZE_STRING) : null;
        $this->initialization = $this->exists('initialization', $data) ? trim(filter_var($data['initialization'], FILTER_SANITIZE_STRING)) : null;
        $this->is_default = $this->exists('is_default', $data) ? filter_var($data['is_default'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->type = $this->exists('type', $data) ? trim(filter_var($data['type'], FILTER_SANITIZE_STRING)) : null;

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
