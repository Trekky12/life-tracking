<?php

namespace App\Domain\Timesheets\ProjectCategoryBudget;

use App\Domain\Main\Utility\DateUtility;

class ProjectCategoryBudget extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT_CATEGORY_BUDGET";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->value = $this->exists('value', $data) ? filter_var($data['value'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->categorization = $this->exists('categorization', $data) ? trim(filter_var($data['categorization'], FILTER_SANITIZE_STRING)) : null;

        $this->warning1 = $this->exists('warning1', $data) ? filter_var($data['warning1'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->warning2 = $this->exists('warning2', $data) ? filter_var($data['warning2'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->warning3 = $this->exists('warning3', $data) ? filter_var($data['warning3'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        $this->main_category = $this->exists('main_category', $data) ? filter_var($data['main_category'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $this->customer = $this->exists('customer', $data) ? filter_var($data['customer'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->start = $this->exists('start', $data) ? $data['start'] : null;
        $this->end = $this->exists('end', $data) ? $data['end'] : null;

        $this->is_hidden = $this->exists('is_hidden', $data) ? filter_var($data['is_hidden'], FILTER_SANITIZE_NUMBER_INT) : 0;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!in_array($this->categorization, array("duration", "duration_modified", "count"))) {
            $this->categorization = "duration";
        }


        /**
         * Set value from request
         */
        $set_value = $this->exists('set_value', $data) ? filter_var($data['set_value'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_value)) {
            $this->value = $this->categorization != "count" ?DateUtility::getSecondsFromDuration($set_value): intval($set_value);
        }

        $set_warning1 = $this->exists('set_warning1', $data) ? filter_var($data['set_warning1'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_warning1)) {
            $this->warning1 = $this->categorization != "count" ? DateUtility::getSecondsFromDuration($set_warning1) : intval($set_warning1);
        }
        $set_warning2 = $this->exists('set_warning2', $data) ? filter_var($data['set_warning2'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_warning2)) {
            $this->warning2 = $this->categorization != "count" ? DateUtility::getSecondsFromDuration($set_warning2): intval($set_warning2);
        }
        $set_warning3 = $this->exists('set_warning3', $data) ? filter_var($data['set_warning3'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($set_warning3)) {
            $this->warning3 = $this->categorization != "count" ? DateUtility::getSecondsFromDuration($set_warning3): intval($set_warning3);
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }

    public function getTimeValue() {
        return DateUtility::splitDateInterval($this->value, true);
    }

}
