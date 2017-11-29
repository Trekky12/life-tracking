<?php

namespace App\Location;

//class Location implements \JsonSerializable {

class Location extends \App\Base\Model{

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');

        $this->identifier = $this->exists('identifier', $data) ? filter_var($data['identifier'], FILTER_SANITIZE_STRING) : null;
        $this->device = $this->exists('device', $data) ? filter_var($data['device'], FILTER_SANITIZE_STRING) : null;
        $this->date = $this->exists('date', $data) ? filter_var($data['date'], FILTER_SANITIZE_STRING) : null;
        $this->time = $this->exists('time', $data) ? filter_var($data['time'], FILTER_SANITIZE_STRING) : null;
        $this->times = $this->exists('times', $data) ? filter_var($data['times'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->ups = $this->exists('ups', $data) ? filter_var($data['ups'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->batt = $this->exists('batt', $data) ? filter_var($data['batt'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->mfield = $this->exists('mfield', $data) ? filter_var($data['mfield'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;

        $this->wifi = $this->exists('wifi', $data) ? filter_var($data['wifi'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->gps = $this->exists('gps', $data) ? filter_var($data['gps'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->screen = $this->exists('screen', $data) ? filter_var($data['screen'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->gps_lat = $this->exists('gps_lat', $data) ? filter_var($data['gps_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->gps_lng = $this->exists('gps_lng', $data) ? filter_var($data['gps_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->gps_acc = $this->exists('gps_acc', $data) ? filter_var($data['gps_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->gps_alt = $this->exists('gps_alt', $data) ? filter_var($data['gps_alt'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->gps_spd = $this->exists('gps_spd', $data) ? filter_var($data['gps_spd'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->gps_tms = $this->exists('gps_tms', $data) ? filter_var($data['gps_tms'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->net_lat = $this->exists('net_lat', $data) ? filter_var($data['net_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->net_lng = $this->exists('net_lng', $data) ? filter_var($data['net_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->net_acc = $this->exists('net_acc', $data) ? filter_var($data['net_acc'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : null;
        $this->net_tms = $this->exists('net_tms', $data) ? filter_var($data['net_tms'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->cell_id = $this->exists('cell_id', $data) ? filter_var($data['cell_id'], FILTER_SANITIZE_STRING) : null;
        $this->cell_sig = $this->exists('cell_sig', $data) ? filter_var($data['cell_sig'], FILTER_SANITIZE_STRING) : null;
        $this->cell_srv = $this->exists('cell_srv', $data) ? filter_var($data['cell_srv'], FILTER_SANITIZE_STRING) : null;
        
        $this->processAdditionalTaskerData($data);
    }

    /**
     * Tasker specific POST values
     */
    private function processAdditionalTaskerData($data) {

        $wifi_state = $this->exists('wifi_state', $data) ? filter_var($data['wifi_state'], FILTER_SANITIZE_STRING) : null;
        if (in_array($wifi_state, array('on', 'off'))) {
            $this->wifi = $wifi_state === 'on' ? 1 : 0;
        }

        $gps_state = $this->exists('gps_state', $data) ? filter_var($data['gps_state'], FILTER_SANITIZE_STRING) : null;
        if (in_array($gps_state, array('on', 'off'))) {
            $this->gps = $gps_state === 'on' ? 1 : 0;
        }

        $screen_state = $this->exists('screen_state', $data) ? filter_var($data['screen_state'], FILTER_SANITIZE_STRING) : null;
        if (in_array($screen_state, array('on', 'off'))) {
            $this->screen = $screen_state === 'on' ? 1 : 0;
        }


        if ($this->exists('gps_loc', $data)) {
            if (preg_match("/^[0-9.]+,[0-9.]+$/", $data['gps_loc'])) {
                $gps_loc = explode(",", $data['gps_loc']);
                if (is_array($gps_loc) && count($gps_loc) == 2) {
                    $this->gps_lat = filter_var($gps_loc[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $this->gps_lng = filter_var($gps_loc[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }
            }
        }

        if ($this->exists('net_loc', $data)) {
            if (preg_match("/^[0-9.]+,[0-9.]+$/", $data['net_loc'])) {
                $net_loc = explode(",", $data['net_loc']);
                if (is_array($net_loc) && count($net_loc) == 2) {
                    $this->net_lat = filter_var($net_loc[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $this->net_lng = filter_var($net_loc[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }
            }
        }
    }

    public function getPosition() {
        return ['id'=> $this->id, 'dt' => $this->dt, 'lat' => $this->net_lat, 'lng' => $this->net_lng, 'acc' => $this->net_acc];
    }

}
