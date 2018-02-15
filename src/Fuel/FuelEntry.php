<?php

namespace App\Fuel;

class FuelEntry extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');
        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->mileage = $this->exists('mileage', $data) ? filter_var($data['mileage'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->price = $this->exists('price', $data) ? filter_var($data['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->volume = $this->exists('volume', $data) ? filter_var($data['volume'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->total_price = $this->exists('total_price', $data) ? filter_var($data['total_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->location = $this->exists('location', $data) ? filter_var($data['location'], FILTER_SANITIZE_STRING) : null;
        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;
        $this->car = $this->exists('car', $data) ? filter_var($data['car'], FILTER_SANITIZE_NUMBER_INT) : null;

        // 0=>full, 1 => partly
        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->distance = $this->exists('distance', $data) ? filter_var($data['distance'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->consumption = $this->exists('consumption', $data) ? filter_var($data['consumption'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;


        /**
         * Set Calc Consumption from request
         */
        $set_calc_consumption = $this->exists('set_calc_consumption', $data) ? filter_var($data['set_calc_consumption'], FILTER_SANITIZE_STRING) : 0;
        $this->calc_consumption = $set_calc_consumption === 'on' ? 1 : 0;

        /**
         * Is there a value in the database?
         */
        $this->calc_consumption = $this->exists('calc_consumption', $data) ? filter_var($data['calc_consumption'], FILTER_SANITIZE_NUMBER_INT) : $this->calc_consumption;

        /**
         * Parsing Errors
         */
        if (!in_array($this->type, array(0, 1))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }
    }

}
