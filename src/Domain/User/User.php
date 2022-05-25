<?php

namespace App\Domain\User;

class User extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_USER";

    public function parseData(array $data) {

        $this->login = $this->exists('login', $data) ? filter_var($data['login'], FILTER_SANITIZE_STRING) : null;
        $this->lastname = $this->exists('lastname', $data) ? filter_var($data['lastname'], FILTER_SANITIZE_STRING) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->mail = $this->exists('mail', $data) ? filter_var($data['mail'], FILTER_SANITIZE_EMAIL) : null;
        $this->role = $this->exists('role', $data) ? filter_var($data['role'], FILTER_SANITIZE_STRING) : null;
        $this->start_url = $this->exists('start_url', $data) ? filter_var($data['start_url'], FILTER_SANITIZE_STRING) : null;

        /**
         * No default value for password, otherwise it will be deleted 
         */
        if ($this->exists('password', $data)) {
            $this->password = filter_var($data['password'], FILTER_SANITIZE_STRING);
        }

        /**
         * Set Password only from request
         */
        $password = $this->exists('set_password', $data) ? filter_var($data['set_password'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($password)) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->exists('image', $data)) {
            $this->image = filter_var($data['image'], FILTER_SANITIZE_STRING);
        }

        if ($this->exists('secret', $data)) {
            $this->secret = filter_var($data['secret'], FILTER_SANITIZE_STRING);
        }

        $this->module_location = $this->exists('module_location', $data) ? filter_var($data['module_location'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_finance = $this->exists('module_finance', $data) ? filter_var($data['module_finance'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_cars = $this->exists('module_cars', $data) ? filter_var($data['module_cars'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_boards = $this->exists('module_boards', $data) ? filter_var($data['module_boards'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_crawlers = $this->exists('module_crawlers', $data) ? filter_var($data['module_crawlers'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_splitbills = $this->exists('module_splitbills', $data) ? filter_var($data['module_splitbills'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_trips = $this->exists('module_trips', $data) ? filter_var($data['module_trips'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_timesheets = $this->exists('module_timesheets', $data) ? filter_var($data['module_timesheets'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_workouts = $this->exists('module_workouts', $data) ? filter_var($data['module_workouts'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_recipes = $this->exists('module_recipes', $data) ? filter_var($data['module_recipes'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->force_pw_change = $this->exists('force_pw_change', $data) ? filter_var($data['force_pw_change'], FILTER_SANITIZE_NUMBER_INT) : 0;
    }

    public function isAdmin() {
        return $this->role == 'admin' ? true : false;
    }

    public function get_thumbnail($size = 'small') {
        if (!empty($this->image)) {
            $file_extension = pathinfo($this->image, PATHINFO_EXTENSION);
            $file_wo_extension = pathinfo($this->image, PATHINFO_FILENAME);
            return $file_wo_extension . '-' . $size . '.' . $file_extension;
        }
        return null;
    }

    public function get_image() {
        if (!empty($this->image)) {
            return $this->image;
        }
        return null;
    }

    public function hasModule($module) {
        switch ($module) {
            case 'finances':
                return $this->module_finance == 1;
            case 'location':
                return $this->module_location == 1;
            case 'cars':
                return $this->module_cars == 1;
            case 'boards':
                return $this->module_boards == 1;
            case 'crawlers':
                return $this->module_crawlers == 1;
            case 'splitbills':
                return $this->module_splitbills == 1;
            case 'trips':
                return $this->module_trips == 1;
            case 'timesheets':
                return $this->module_timesheets == 1;
            case 'workouts':
                return $this->module_workouts == 1;
            case 'recipes':
                return $this->module_recipes == 1;
        }

        return false;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->login;
    }

    public function get_fields($remove_user_element = false, $insert = true, $update = false) {
        $temp = parent::get_fields($remove_user_element, $insert, $update);

        if($insert || $update){
            return $temp;
        }

        return [
            "id" => $this->id,
            "login" => $this->login,
            "lastname" => $this->lastname,
            "name" => $this->name
        ];
    }


}
