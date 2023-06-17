<?php

namespace App\Domain\Car\Service;

class CarServiceEntry extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_CARS_SERVICE_ENTRY";

    public function parseData(array $data) {

        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : date('Y-m-d');
        $this->mileage = $this->exists('mileage', $data) ? filter_var($data['mileage'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->notice = $this->exists('notice', $data) ? filter_var($data['notice'], FILTER_SANITIZE_STRING) : null;
        $this->car = $this->exists('car', $data) ? filter_var($data['car'], FILTER_SANITIZE_NUMBER_INT) : null;

        // 0 => refuel, 1 => service
        $this->type = $this->exists('type', $data) ? filter_var($data['type'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->fuel_price = $this->exists('fuel_price', $data) ? filter_var($data['fuel_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->fuel_volume = $this->exists('fuel_volume', $data) ? filter_var($data['fuel_volume'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->fuel_total_price = $this->exists('fuel_total_price', $data) ? filter_var($data['fuel_total_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->fuel_location = $this->exists('fuel_location', $data) ? filter_var($data['fuel_location'], FILTER_SANITIZE_STRING) : null;

        // 0=>full, 1 => partly
        $this->fuel_type = $this->exists('fuel_type', $data) ? filter_var($data['fuel_type'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->fuel_distance = $this->exists('fuel_distance', $data) ? filter_var($data['fuel_distance'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->fuel_consumption = $this->exists('fuel_consumption', $data) ? filter_var($data['fuel_consumption'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->fuel_calc_consumption = $this->exists('fuel_calc_consumption', $data) ? filter_var($data['fuel_calc_consumption'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->service_oil_before = $this->exists('service_oil_before', $data) ? filter_var($data['service_oil_before'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->service_oil_after = $this->exists('service_oil_after', $data) ? filter_var($data['service_oil_after'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->service_water_wiper_before = $this->exists('service_water_wiper_before', $data) ? filter_var($data['service_water_wiper_before'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->service_water_wiper_after = $this->exists('service_water_wiper_after', $data) ? filter_var($data['service_water_wiper_after'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->service_tire_change = $this->exists('service_tire_change', $data) ? filter_var($data['service_tire_change'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->service_garage = $this->exists('service_garage', $data) ? filter_var($data['service_garage'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->service_air_front_left_before = $this->exists('service_air_front_left_before', $data) ? filter_var($data['service_air_front_left_before'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_front_left_after = $this->exists('service_air_front_left_after', $data) ? filter_var($data['service_air_front_left_after'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_front_right_before = $this->exists('service_air_front_right_before', $data) ? filter_var($data['service_air_front_right_before'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_front_right_after = $this->exists('service_air_front_right_after', $data) ? filter_var($data['service_air_front_right_after'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_back_left_before = $this->exists('service_air_back_left_before', $data) ? filter_var($data['service_air_back_left_before'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_back_left_after = $this->exists('service_air_back_left_after', $data) ? filter_var($data['service_air_back_left_after'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_back_right_before = $this->exists('service_air_back_right_before', $data) ? filter_var($data['service_air_back_right_before'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->service_air_back_right_after = $this->exists('service_air_back_right_after', $data) ? filter_var($data['service_air_back_right_after'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        // new entry --> save createdBy
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }
        // get value from db
        if ($this->exists('createdBy', $data)) {
            $this->createdBy = filter_var($data['createdBy'], FILTER_SANITIZE_NUMBER_INT);
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->lat = $this->exists('lat', $data) ? filter_var($data['lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->lng = $this->exists('lng', $data) ? filter_var($data['lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->acc = $this->exists('acc', $data) ? filter_var($data['acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        /**
         * Parsing Errors
         */
        if (!in_array($this->type, array(0, 1))) {
            $this->parsing_errors[] = "WRONG_TYPE";
        }
    }

    public function isServiceOil() {
        return (!is_null($this->service_oil_before) && $this->service_oil_before != 0) ||
                (!is_null($this->service_oil_after) && $this->service_oil_after != 0);
    }

    public function isServiceWaterWiper() {
        return (!is_null($this->service_water_wiper_before) && $this->service_water_wiper_before != 0) ||
                (!is_null($this->service_water_wiper_after) && $this->service_water_wiper_after != 0);
    }

    public function isServiceAir() {
        return $this->service_air_front_left_before > 0 ||
                $this->service_air_front_left_after > 0 ||
                $this->service_air_front_right_before > 0 ||
                $this->service_air_front_right_after > 0 ||
                $this->service_air_back_left_before > 0 ||
                $this->service_air_back_left_after > 0 ||
                $this->service_air_back_right_before > 0 ||
                $this->service_air_back_right_after > 0;
    }

    public function isServiceTireChange() {
        return !is_null($this->service_tire_change) && $this->service_tire_change != 0;
    }

    public function isServiceGarage() {
        return !is_null($this->service_garage) && $this->service_garage != 0;
    }

    public function getPosition() {
        return [
            'id' => $this->id,
            'dt' => $this->date,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'acc' => $this->acc,
            'description' => $this->type,
            'type' => 2];
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        $refuel = $translator->getTranslatedString("CAR_REFUEL");
        $service = $translator->getTranslatedString("CAR_SERVICE");
        return sprintf("%s (%s)", $this->mileage, $this->type == 0 ? $refuel : $service);
    }

    public function getParentID() {
        return $this->car;
    }

}
