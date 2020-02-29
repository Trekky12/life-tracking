<?php

namespace App\Main;

use App\Base\Settings;

class Translator {

    protected $settings;

    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }

    public function getLanguage() {
        $selected_language = $this->settings->getAppSettings()['i18n']['template'];
        $lang = require __DIR__ . '/../lang/' . $selected_language . '.php';
        return $lang;
    }

    public function getTranslatedString($key) {
        $lang = $this->getLanguage();
        return array_key_exists($key, $lang) ? $lang[$key] : $key;
    }

}
