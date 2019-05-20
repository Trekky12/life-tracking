<?php

namespace App\Crawler\CrawlerDataset;

class CrawlerDataset extends \App\Base\Model {

    public function parseData(array $data) {

        // new dataset --> save createdBy
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->identifier = $this->exists('identifier', $data) ? filter_var($data['identifier'], FILTER_SANITIZE_STRING) : null;
        $this->crawler = $this->exists('crawler', $data) ? filter_var($data['crawler'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->data = $this->exists('data', $data) ? $data['data'] : null;
        $this->diff = $this->exists('diff', $data) ? $data['diff'] : null;

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

}
