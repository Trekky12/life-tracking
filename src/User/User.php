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
    }
    
    public function isAdmin(){
        return $this->role == 'admin' ? true : false;
    }

}
