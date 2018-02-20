<?php

namespace App\User;

class User extends \App\Base\Model {

    public function parseData(array $data) {

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $this->dt = $this->exists('dt', $data) ? $data['dt'] : date('Y-m-d G:i:s');

        $this->login = $this->exists('login', $data) ? filter_var($data['login'], FILTER_SANITIZE_STRING) : null;
        $this->lastname = $this->exists('lastname', $data) ? filter_var($data['lastname'], FILTER_SANITIZE_STRING) : null;
        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->mail = $this->exists('mail', $data) ? filter_var($data['mail'], FILTER_SANITIZE_EMAIL) : null;
        $this->role = $this->exists('role', $data) ? filter_var($data['role'], FILTER_SANITIZE_STRING) : null;

        /**
         * No default value for password, otherwise it will be deleted 
         */
        if($this->exists('password', $data)){
            $this->password = filter_var($data['password'], FILTER_SANITIZE_STRING);
        }

        /**
         * Set Password only from request
         */
        $password = $this->exists('setpassword', $data) ? filter_var($data['setpassword'], FILTER_SANITIZE_STRING) : null;
        if (!is_null($password)) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
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

    }
    
    public function isAdmin(){
        return $this->role == 'admin' ? true : false;
    }


}
