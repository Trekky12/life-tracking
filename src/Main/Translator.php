<?php

namespace App\Main;

use Psr\Container\ContainerInterface;

class Translator {

    protected $settings;

    public function __construct(ContainerInterface $ci) {
        $this->settings = $ci->get('settings');
    }

    public function getLanguage() {
        $selected_language = $this->settings['app']['i18n']['template'];
        $lang = require __DIR__ . '/../lang/' . $selected_language . '.php';
        return $lang;
    }

    public function getTranslatedString($key) {
        $lang = $this->getLanguage();
        return array_key_exists($key, $lang) ? $lang[$key] : $key;
    }

}
