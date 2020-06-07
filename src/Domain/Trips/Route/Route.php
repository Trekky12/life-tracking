<?php

namespace App\Domain\Trips\Route;

class Route extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TRIPS_ROUTE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->trip = $this->exists('trip', $data) ? filter_var($data['trip'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;

        $this->start_date = $this->exists('start_date', $data) ? filter_var($data['start_date'], FILTER_SANITIZE_STRING) : null;
        $this->end_date = $this->exists('end_date', $data) ? filter_var($data['end_date'], FILTER_SANITIZE_STRING) : null;

        $this->waypoints = $this->exists('waypoints', $data) ? $data['waypoints'] : null;
        
        $this->profile = $this->exists('profile', $data) ? filter_var($data['profile'], FILTER_SANITIZE_STRING) : null;

        /**
         * Clean date/time
         */
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->start_date)) {
            $this->start_date = null;
        }
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->end_date)) {
            $this->end_date = null;
        }

        // set end date to same date like start if end date is empty
        if (!empty($this->start_date) && empty($this->end_date)) {
            $this->end_date = $this->start_date;
        }

        // if start date is greater than end date swap both
        if (!empty($this->start_date) && !empty($this->end_date)) {
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);

            if ($start > $end) {
                $this->start_date = $end->format('Y-m-d');
                $this->end_date = $start->format('Y-m-d');
            }
        }

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->trip;
    }

    public function getWaypoints() {
        return json_decode($this->waypoints, true);
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        if ($insert || $update) {
            $temp["waypoints"] = json_encode($this->waypoints);
        }

        return $temp;
    }

}
