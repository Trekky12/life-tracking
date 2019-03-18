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
        
        /**
         * Values from DB
         */
        if ($this->exists('createdBy', $data)) {
            $this->createdBy = filter_var($data['createdBy'], FILTER_SANITIZE_NUMBER_INT);
        }
        if ($this->exists('createdOn', $data)) {
            $this->createdOn = filter_var($data['createdOn'], FILTER_SANITIZE_STRING);
        }
        if ($this->exists('changedBy', $data)) {
            $this->changedBy = filter_var($data['changedBy'], FILTER_SANITIZE_NUMBER_INT);
        }
    }

    public function getDataValue($field) {
        $data = json_decode($this->data, true);
        return array_key_exists($field, $data) ? $data[$field] : null;
    }

}
