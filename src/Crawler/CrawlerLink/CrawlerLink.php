<?php

namespace App\Crawler\CrawlerLink;

class CrawlerLink extends \App\Base\Model {

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->crawler = $this->exists('crawler', $data) ? filter_var($data['crawler'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->name = $this->exists('name', $data) ? filter_var($data['name'], FILTER_SANITIZE_STRING) : null;
        $this->link = $this->exists('link', $data) ? filter_var($data['link'], FILTER_SANITIZE_STRING) : null;
        $this->parent = $this->exists('parent', $data) ? filter_var($data['parent'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;

        if (empty($this->name)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

}
