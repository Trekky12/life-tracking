<?php

namespace App\Domain\Timesheets\Reminder;

use App\Domain\Main\Utility\Utility;

class Reminder extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_REMINDER";

    public function parseData(array $data) {

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->trigger_type  = $this->exists('trigger_type', $data) ? Utility::filter_string_polyfill($data['trigger_type']) : null;
        $this->title = $this->exists('title', $data) ? Utility::filter_string_polyfill($data['title']) : null;

        $this->category = $this->exists('category', $data) ? filter_var($data['category'], FILTER_SANITIZE_NUMBER_INT) : null;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }

        if (!in_array($this->trigger_type, ["after_last_sheet_plus_1h", "after_last_sheet", "after_each_sheet"])) {
            $this->trigger_type  = "after_last_sheet_plus_1h";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->project;
    }

    /**
     * Remove fields from the notification category
     */
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        unset($temp["category"]);

        return $temp;
    }

}
