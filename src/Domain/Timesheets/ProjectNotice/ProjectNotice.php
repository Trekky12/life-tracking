<?php

namespace App\Domain\Timesheets\ProjectNotice;

use App\Domain\Main\Utility\Utility;

class ProjectNotice extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_PROJECT_NOTICE";

    public function parseData(array $data) {

        // new dataset --> save createdBy 
        if (!$this->exists('id', $data)) {
            $this->createdBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;
        }
        // is later overwritten with db value (if exists)
        $this->changedBy = $this->exists('user', $data) ? filter_var($data['user'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->project = $this->exists('project', $data) ? filter_var($data['project'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->notice = $this->exists('notice', $data) ? Utility::filter_string_polyfill($data['notice']) : null;
        $this->encryptedCEK = $this->exists('encryptedCEK', $data) ? Utility::filter_string_polyfill($data['encryptedCEK']) : null;


        $this->is_autosave = $this->exists('is_autosave', $data) ? intval(filter_var($data['is_autosave'], FILTER_SANITIZE_NUMBER_INT)) : 0;
    }

    public function getParentID() {
        return $this->project;
    }

}
