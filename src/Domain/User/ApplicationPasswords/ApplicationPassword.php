<?php

namespace App\Domain\User\ApplicationPasswords;

use App\Domain\Main\Utility\Utility;

class ApplicationPassword extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_APPLICATIONPASSWORD";

    public function parseData(array $data) {

        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;

        /**
         * password from database, no default value for password, otherwise it will be deleted 
         */
        if ($this->exists('password', $data)) {
            $this->password = Utility::filter_string_polyfill($data['password']);
        }

        /**
         * Set Password only from request
         */
        $password = $this->exists('set_password', $data) ? Utility::filter_string_polyfill($data['set_password']) : null;
        if (!is_null($password)) {
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->name;
    }

    public function getParentID() {
        return $this->user;
    }

    public static function createPassword() {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pw = [];
        for ($i = 0; $i < 5; $i++) {
            $pw[] = substr(str_shuffle($permitted_chars), 0, 4);
        }

        $password = implode("-", $pw);

        return $password;
    }

}
