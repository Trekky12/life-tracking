<?php

namespace App\Trips\Event;

class Event extends \App\Base\Model {

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->trip = $this->exists('trip', $data) ? filter_var($data['trip'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->start_date = $this->exists('start_date', $data) ? filter_var($data['start_date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->start_time = $this->exists('start_time', $data) ? filter_var($data['start_time'], FILTER_SANITIZE_STRING) : null;
        $this->start_address = $this->exists('start_address', $data) ? filter_var($data['start_address'], FILTER_SANITIZE_STRING) : null;
        $this->start_lat = $this->exists('start_lat', $data) ? filter_var($data['start_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->start_lng = $this->exists('start_lng', $data) ? filter_var($data['start_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->end_date = $this->exists('end_date', $data) ? filter_var($data['end_date'], FILTER_SANITIZE_STRING) : null;
        $this->end_time = $this->exists('end_time', $data) ? filter_var($data['end_time'], FILTER_SANITIZE_STRING) : null;
        $this->end_address = $this->exists('end_address', $data) ? filter_var($data['end_address'], FILTER_SANITIZE_STRING) : null;
        $this->end_lat = $this->exists('end_lat', $data) ? filter_var($data['end_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->end_lng = $this->exists('end_lng', $data) ? filter_var($data['end_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_STRING) : null;

        if (!in_array($this->type, array_keys(\App\Trips\Event\Controller::eventTypes()))) {
            $this->type = null;
        }

        /**
         * Clean date/time
         */
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->start_date)) {
            $this->start_date = date('Y-m-d');
        }
        if (!preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->start_time)) {
            $this->start_time = null;
        }
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->end_date)) {
            $this->end_date = null;
        }
        if (!preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->end_time)) {
            $this->end_time = null;
        }


        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
