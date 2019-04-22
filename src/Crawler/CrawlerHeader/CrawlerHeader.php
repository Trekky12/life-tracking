<?php

namespace App\Crawler\CrawlerHeader;

class CrawlerHeader extends \App\Base\Model {

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }

        $this->crawler = $this->exists('crawler', $data) ? filter_var($data['crawler'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->headline = $this->exists('headline', $data) ? filter_var($data['headline'], FILTER_SANITIZE_STRING) : null;
        $this->field_name = $this->exists('field_name', $data) ? filter_var($data['field_name'], FILTER_SANITIZE_STRING) : null;
        $this->field_link = $this->exists('field_link', $data) ? filter_var($data['field_link'], FILTER_SANITIZE_STRING) : null;
        $this->field_content = $this->exists('field_content', $data) ? filter_var($data['field_content'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $this->sortable = $this->exists('sortable', $data) ? filter_var($data['sortable'], FILTER_SANITIZE_NUMBER_INT) : 0;
        
        $set_sortable = $this->exists('set_sortable', $data) ? filter_var($data['set_sortable'], FILTER_SANITIZE_STRING) : 0;
        $this->sortable = $set_sortable === 'on' ? 1 : 0;
        $this->sortable = $this->exists('sortable', $data) ? filter_var($data['sortable'], FILTER_SANITIZE_NUMBER_INT) : $this->sortable;
        
        $this->diff = $this->exists('diff', $data) ? filter_var($data['diff'], FILTER_SANITIZE_NUMBER_INT) : 0;
        
        $set_diff = $this->exists('set_diff', $data) ? filter_var($data['set_diff'], FILTER_SANITIZE_STRING) : 0;
        $this->diff = $set_diff === 'on' ? 1 : 0;
        $this->diff = $this->exists('diff', $data) ? filter_var($data['diff'], FILTER_SANITIZE_NUMBER_INT) : $this->diff;
        
        $this->prefix = $this->exists('prefix', $data) ? filter_var($data['prefix'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
        $this->suffix = $this->exists('suffix', $data) ? filter_var($data['suffix'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        $this->position = $this->exists('position', $data) ? filter_var($data['position'], FILTER_SANITIZE_NUMBER_INT) : 999;
        
        $this->sort = $this->exists('sort', $data) ? filter_var($data['sort'], FILTER_SANITIZE_STRING) : null;

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

        if (empty($this->headline)) {
            $this->parsing_errors[] = "NAME_CANNOT_BE_EMPTY";
        }
    }

    public function getHTML($element = "field") {
        $field = $this->field_content;
        switch($element){
            case "prefix":
                $field = $this->prefix;
                break;
            case "suffix":
                $field = $this->suffix;
                break;
        }
        return htmlspecialchars_decode($field);
    }
    
}