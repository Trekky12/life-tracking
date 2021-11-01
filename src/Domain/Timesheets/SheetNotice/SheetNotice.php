<?php

namespace App\Domain\Timesheets\SheetNotice;

class SheetNotice extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_SHEET_NOTICE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }
        // is later overwritten with db value (if exists)
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->sheet = $this->exists('sheet', $data) ? filter_var($data['sheet'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->notice = $this->exists('notice', $data) ? trim(filter_var($data['notice'], FILTER_SANITIZE_STRING)) : null;
        
        $this->encrypted = $this->exists('encrypted', $data) ? intval(filter_var($data['encrypted'], FILTER_SANITIZE_STRING)) : 0;

    }
    
    public function getNotice(){
        return $this->notice;
    }

    public function getParentID() {
        return $this->sheet;
    }

}
