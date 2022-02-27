<?php

namespace App\Domain\Board\Card;

class Card extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_BOARDS_CARD";

    public function parseData(array $data) {

        $this->title = $this->exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        // new card --> save createdBy and hash
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
            $this->hash = $this->exists('hash', $data) ? filter_var($data['hash'], FILTER_SANITIZE_SPECIAL_CHARS) : hash('CRC32', time() . rand(0, 1000000) . $this->title);
        }

        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : null;
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->stack = $this->exists('stack', $data) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        /**
         * Clean date/time
         */
        if (!empty($this->date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!empty($this->time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        if (empty($this->title)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->title;
    }

    public function getParentID() {
        return $this->stack;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {

        $temp = parent::get_fields($remove_user_element, $insert, $update);
        
        if ($temp["description"]) {
            $temp["description"] = html_entity_decode(htmlspecialchars_decode($temp["description"]));
        }
        if ($temp["title"]) {
            $temp["title"] = html_entity_decode(htmlspecialchars_decode($temp["title"]));
        }

        return $temp;
    }

}
