<?php

namespace App\User;

class User extends \App\Base\Model {

    static $MODEL_NAME = "MODEL_USER";

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

        $this->module_location = $this->exists('module_location', $data) ? filter_var($data['module_location'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_finance = $this->exists('module_finance', $data) ? filter_var($data['module_finance'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_cars = $this->exists('module_cars', $data) ? filter_var($data['module_cars'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_boards = $this->exists('module_boards', $data) ? filter_var($data['module_boards'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_crawlers = $this->exists('module_crawlers', $data) ? filter_var($data['module_crawlers'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_splitbills = $this->exists('module_splitbills', $data) ? filter_var($data['module_splitbills'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_trips = $this->exists('module_trips', $data) ? filter_var($data['module_trips'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->module_timesheets = $this->exists('module_timesheets', $data) ? filter_var($data['module_timesheets'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->force_pw_change = $this->exists('force_pw_change', $data) ? filter_var($data['force_pw_change'], FILTER_SANITIZE_NUMBER_INT) : 0;

        $this->mails_user = $this->exists('mails_user', $data) ? filter_var($data['mails_user'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->mails_finances = $this->exists('mails_finances', $data) ? filter_var($data['mails_finances'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->mails_board = $this->exists('mails_board', $data) ? filter_var($data['mails_board'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->mails_board_reminder = $this->exists('mails_board_reminder', $data) ? filter_var($data['mails_board_reminder'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $this->mails_splitted_bills = $this->exists('mails_splitted_bills', $data) ? filter_var($data['mails_splitted_bills'], FILTER_SANITIZE_NUMBER_INT) : 0;
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
        }

        return false;
    }

    public function getDescription(\App\Main\Translator $translator, array $settings) {
        return $this->login;
    }

}
