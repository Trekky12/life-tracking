<?php

namespace App\User;

class User extends \App\Base\Model {

    public function parseData(array $data) {

        $this->login = $this->exists('login', $data) ? filter_var($data['login'], FILTER_SANITIZE_STRING) : null;
        $this->lastname = $this->exists('lastname', $data) ? filter_var($data['lastname'], FILTER_SANITIZE_STRING) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->mail = $this->exists('mail', $data) ? filter_var($data['mail'], FILTER_SANITIZE_EMAIL) : null;
        $this->role = $this->exists('role', $data) ? filter_var($data['role'], FILTER_SANITIZE_STRING) : null;

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

        if($this->exists('image', $data)){
            $this->image = filter_var($data['image'], FILTER_SANITIZE_STRING);
        }

        $set_module_location = $this->exists('set_module_location', $data) ? filter_var($data['set_module_location'], FILTER_SANITIZE_STRING) : 0;
        $this->module_location = $set_module_location === 'on' ? 1 : 0;
        $this->module_location = $this->exists('module_location', $data) ? filter_var($data['module_location'], FILTER_SANITIZE_NUMBER_INT) : $this->module_location;

        $set_module_finance = $this->exists('set_module_finance', $data) ? filter_var($data['set_module_finance'], FILTER_SANITIZE_STRING) : 0;
        $this->module_finance = $set_module_finance === 'on' ? 1 : 0;
        $this->module_finance = $this->exists('module_finance', $data) ? filter_var($data['module_finance'], FILTER_SANITIZE_NUMBER_INT) : $this->module_finance;

        $set_module_fuel = $this->exists('set_module_fuel', $data) ? filter_var($data['set_module_fuel'], FILTER_SANITIZE_STRING) : 0;
        $this->module_fuel = $set_module_fuel === 'on' ? 1 : 0;
        $this->module_fuel = $this->exists('module_fuel', $data) ? filter_var($data['module_fuel'], FILTER_SANITIZE_NUMBER_INT) : $this->module_fuel;

        $set_module_boards = $this->exists('set_module_boards', $data) ? filter_var($data['set_module_boards'], FILTER_SANITIZE_STRING) : 0;
        $this->module_boards = $set_module_boards === 'on' ? 1 : 0;
        $this->module_boards = $this->exists('module_boards', $data) ? filter_var($data['module_boards'], FILTER_SANITIZE_NUMBER_INT) : $this->module_boards;
        
        $set_force_pw_change = $this->exists('set_force_pw_change', $data) ? filter_var($data['set_force_pw_change'], FILTER_SANITIZE_STRING) : 1;
        $this->force_pw_change = $set_force_pw_change === 'on' ? 1 : 0;
        $this->force_pw_change = $this->exists('force_pw_change', $data) ? filter_var($data['force_pw_change'], FILTER_SANITIZE_NUMBER_INT) : $this->force_pw_change;
        
        $set_mails_user = $this->exists('set_mails_user', $data) ? filter_var($data['set_mails_user'], FILTER_SANITIZE_STRING) : 1;
        $this->mails_user = $set_mails_user === 'on' ? 1 : 0;
        $this->mails_user = $this->exists('mails_user', $data) ? filter_var($data['mails_user'], FILTER_SANITIZE_NUMBER_INT) : $this->mails_user;
        
        $set_mails_finances = $this->exists('set_mails_finances', $data) ? filter_var($data['set_mails_finances'], FILTER_SANITIZE_STRING) : 1;
        $this->mails_finances = $set_mails_finances === 'on' ? 1 : 0;
        $this->mails_finances = $this->exists('mails_finances', $data) ? filter_var($data['mails_finances'], FILTER_SANITIZE_NUMBER_INT) : $this->mails_finances;
        
        $set_mails_board = $this->exists('set_mails_board', $data) ? filter_var($data['set_mails_board'], FILTER_SANITIZE_STRING) : 1;
        $this->mails_board = $set_mails_board === 'on' ? 1 : 0;
        $this->mails_board = $this->exists('mails_board', $data) ? filter_var($data['mails_board'], FILTER_SANITIZE_NUMBER_INT) : $this->mails_board;
        
        $set_mails_board_reminder = $this->exists('set_mails_board_reminder', $data) ? filter_var($data['set_mails_board_reminder'], FILTER_SANITIZE_STRING) : 1;
        $this->mails_board_reminder = $set_mails_board_reminder === 'on' ? 1 : 0;
        $this->mails_board_reminder = $this->exists('mails_board_reminder', $data) ? filter_var($data['mails_board_reminder'], FILTER_SANITIZE_NUMBER_INT) : $this->mails_board_reminder;
        

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

}
