<?php

namespace App\Domain\Timesheets\ProjectCategory;

class ProjectCategory extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT_CATEGORY";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }

}
