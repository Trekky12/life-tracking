<?php

namespace App\Domain\User\MobileFavorites;

class MobileFavorite extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_MOBILEFAVORITE";

    public function parseData(array $data) {

        $this->url = $this->exists('url', $data) ? filter_var($data['url'], FILTER_SANITIZE_STRING) : null;
        $this->icon = $this->exists('icon', $data) ? filter_var($data['icon'], FILTER_SANITIZE_STRING) : null;
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
    }

    public function getURL() {

        $current_date = new \DateTime('now');

        $url = $this->url;
        $url = str_replace("\$day\$", $current_date->format("d"), $url);
        $url = str_replace("\$month\$", $current_date->format("m"), $url);
        $url = str_replace("\$year\$", $current_date->format("Y"), $url);

        return $url;
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->url;
    }

    public function getParentID() {
        return $this->user;
    }

}
