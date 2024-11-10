<?php

namespace App\Domain\Timesheets\Customer;

class Customer extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_CUSTOMER";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->background_color = $this->exists('background_color', $data) ? filter_var($data['background_color'], FILTER_SANITIZE_STRING) : null;
        $this->text_color = $this->exists('text_color', $data) ? filter_var($data['text_color'], FILTER_SANITIZE_STRING) : null;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!preg_match("/^#[a-f0-9]{6}$/i", $this->background_color) || !preg_match("/^#[a-f0-9]{6}$/i", $this->text_color)) {
            $this->parsing_errors[] = "WRONG_COLOR_TYPE";
        }

        // Reset default values if no change in any of the fields
        if (strcmp(strtoupper($this->background_color), "#FFFFFF") === 0 && strcmp(strtoupper($this->text_color), "#000000") === 0) {
            $this->background_color = null;
            $this->text_color = null;
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }
}
