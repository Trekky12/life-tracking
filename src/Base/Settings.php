<?php

namespace App\Base;

class Settings {

    private $settings = [];

    public function __construct(array $data) {
        $this->settings = $data;
    }

    public function all() {
        return $this->settings;
    }

    public function getAppSettings() {
        return $this->settings['app'];
    }

}
