<?php

namespace App\Splitbill\Bill;

class Bill extends \App\Base\Model {

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->sbgroup = $this->exists('sbgroup', $data) ? filter_var($data['sbgroup'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : date('H:i:s');
        
        $this->lat = $this->exists('lat', $data) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->lng = $this->exists('lng', $data) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->acc = $this->exists('acc', $data) ? filter_var($data['acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        
        $this->notice = $this->exists('notice', $data) ? trim(filter_var($data['notice'], FILTER_SANITIZE_STRING)) : null;
        
        $this->settleup = $this->exists('settleup', $data) ? intval(filter_var($data['settleup'], FILTER_SANITIZE_NUMBER_INT)) : 0;
        
        /**
         * Clean date/time
         */
        if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $this->date)) {
            $this->date = date('Y-m-d');
        }

        if (!preg_match("/^[0-9]{2}:[0-9]{2}(:[0-9]{2})?$/", $this->time)) {
            $this->time = date('H:i:s');
        }

        if (empty($this->name) && $this->settleup == 0) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }


        $this->spend = $this->exists('spend', $data) ? filter_var($data['spend'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->paid = $this->exists('paid', $data) ? filter_var($data['paid'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $this->balance = $this->exists('balance', $data) ? filter_var($data['balance'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
    }

    /**
     * Remove fields which are not in the db table
     */
    public function get_fields($removeUser = false) {
        $temp = parent::get_fields($removeUser);

        unset($temp["spend"]);
        unset($temp["paid"]);
        unset($temp["balance"]);

        return $temp;
    }

}
