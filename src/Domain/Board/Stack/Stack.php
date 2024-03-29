<?php

namespace App\Domain\Board\Stack;

class Stack extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_BOARDS_STACK";

    public function parseData(array $data) {

        // new stack --> save createdBy
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_SPECIAL_CHARS) : null;

        $this->board = $this->exists('board', $data) ? filter_var($data['board'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->board;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {

        $temp = parent::get_fields($remove_user_element, $insert, $update);
        
        if ($temp["name"]) {
            $temp["name"] = html_entity_decode(htmlspecialchars_decode($temp["name"]));
        }

        return $temp;
    }

}
