<?php

namespace App\Board\Stack;

class Stack extends \App\Base\DataObject {

    static $NAME = "DATAOBJECT_BOARDS_STACK";

    public function parseData(array $data) {

        // new stack --> save createdBy
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->board = $this->exists('board', $data) ? filter_var($data['board'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Main\Translator $translator, \App\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->board;
    }

}
