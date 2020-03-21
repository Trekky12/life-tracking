<?php

namespace App\Main\Utility;

class SessionUtility {

    public static function setSessionVar($key, $var) {
        $_SESSION[$key] = $var;
    }

    public static function getSessionVar($key, $fallback = null) {
        return array_key_exists($key, $_SESSION) ? filter_var($_SESSION[$key]) : $fallback;
    }

    public static function deleteSessionVar($key) {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

}
