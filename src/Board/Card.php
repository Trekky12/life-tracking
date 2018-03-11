<?php

namespace App\Board;

class Card extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->changedOn = $this->exists('changedOn', $data) ? $data['changedOn'] : date('Y-m-d G:i:s');
        $this->title = $this->exists('title', $data) ? filter_var($data['title'], FILTER_SANITIZE_STRING) : null;

        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : null;
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : null;
        $this->description = $this->exists('description', $data) ? filter_var($data['description'], FILTER_SANITIZE_STRING) : null;
        
        $this->stack = $this->exists('stack', $data) ? filter_var($data['stack'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
        
        $this->archive = $this->exists('archive', $data) ? filter_var($data['archive'], FILTER_SANITIZE_NUMBER_INT) : 0;

        /**
         * Clean date/time
         */
        if (!is_null($this->date) && !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!is_null($this->time) && !preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        if (empty($this->title)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
