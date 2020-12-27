<?php

namespace App\Domain\Workouts\Session;

class Session extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_WORKOUTS_SESSION";

    public function parseData(array $data) {

        $this->plan = $this->exists('plan', $data) ? filter_var($data['plan'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : null;
        $this->start_time = $this->exists('start_time', $data) ? filter_var($data['start_time'], FILTER_SANITIZE_STRING) : null;
        $this->end_time = $this->exists('end_time', $data) ? filter_var($data['end_time'], FILTER_SANITIZE_STRING) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;

        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }
        
        $this->days = $this->exists('days', $data) ? filter_var($data['days'], FILTER_SANITIZE_STRING) : null;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->date;
    }

    public function getParentID() {
        return $this->plan;
    }
    
    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);
        
        unset($temp["days"]);
        
        return $temp;
    }

}
