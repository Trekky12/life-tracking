<?php

namespace App\Domain\Timesheets\RequirementType;

use App\Domain\Main\Utility\Utility;

class RequirementType extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_REQUIREMENT_TYPE";

    public function parseData(array $data) {

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $this->datatype = $this->exists('datatype', $data) ? Utility::filter_string_polyfill($data['datatype']) : null;
        $this->initialization = $this->exists('initialization', $data) ? Utility::filter_string_polyfill($data['initialization']) : null;
        $this->validity_period = $this->exists('validity_period', $data) ? Utility::filter_string_polyfill($data['validity_period']) : null;
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!in_array($this->validity_period, ["month", "quarter", "year"])) {
            $this->validity_period = "month";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }
}
