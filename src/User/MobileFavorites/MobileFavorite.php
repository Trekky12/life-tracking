<?php

namespace App\User\MobileFavorites;

class MobileFavorite extends \App\Base\Model {

    public function parseData(array $data) {

        $this->url = $this->exists('url', $data) ? filter_var($data['url'], FILTER_SANITIZE_STRING) : null;
        $this->icon = $this->exists('icon', $data) ? filter_var($data['icon'], FILTER_SANITIZE_STRING) : null;
        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

    }

}
