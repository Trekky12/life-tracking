<?php

namespace App\Domain\Crawler\CrawlerDataset;

use App\Domain\Main\Utility\Utility;

class CrawlerDataset extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_CRAWLERS_DATASET";

    public function parseData(array $data) {

        // new dataset --> save createdBy
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->identifier = $this->exists('identifier', $data) ? Utility::filter_string_polyfill($data['identifier']) : null;
        $this->crawler = $this->exists('crawler', $data) ? filter_var($data['crawler'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->data = $this->exists('data', $data) ? $data['data'] : null;
        $this->diff = $this->exists('diff', $data) ? $data['diff'] : null;

        $this->saved = $this->exists('saved', $data) ? filter_var($data['saved'], FILTER_SANITIZE_NUMBER_INT) : 0;
    }

    public function getDataValue($field, $type = "data") {
        $data = $this->getData($type);
        return is_array($data) && array_key_exists($field, $data) ? $data[$field] : null;
    }

    public function getData($type = "data") {
        if ($type === "diff") {
            return json_decode($this->diff, true);
        }
        return json_decode($this->data, true);
    }

    public function getDescription(\App\Domain\Main\Translator $translator, \App\Domain\Base\Settings $settings) {
        return $this->identifier;
    }

    public function getParentID() {
        return $this->crawler;
    }

    public function isSaved() {
        return intval($this->saved) == 1;
    }

}
