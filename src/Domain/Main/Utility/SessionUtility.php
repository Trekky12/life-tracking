<?php

namespace App\Domain\Main\Utility;

class SessionUtility {

    public static function setSessionVar($key, $var) {
        $_SESSION[$key] = $var;
    }

    public static function getSessionVar($key, $fallback = null) {
        if(!array_key_exists($key, $_SESSION)){
            return $fallback;
        }
        if(is_array($_SESSION[$key])){
            return $_SESSION[$key];
        }
        return filter_var($_SESSION[$key]);
    }

    public static function deleteSessionVar($key) {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

}
