<?php

namespace App\Domain\Timesheets\SheetFile;

use App\Domain\Main\Utility\Utility;

class SheetFile extends \App\Domain\DataObject {

    static $NAME = "DATAOBJECT_TIMESHEETS_SHEET_FILE";

    public function parseData(array $data) {
        $this->sheet = $this->exists('sheet', $data) ? filter_var($data['sheet'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->name = $this->exists('name', $data) ? Utility::filter_string_polyfill($data['name']) : null;
        $this->type = $this->exists('type', $data) ? Utility::filter_string_polyfill($data['type']) : null;
        $this->filename = $this->exists('filename', $data) ? Utility::filter_string_polyfill($data['filename']) : null;
        $this->encryptedCEK = $this->exists('encryptedCEK', $data) ? Utility::filter_string_polyfill($data['encryptedCEK']) : null;
    }

    public function getParentID() {
        return $this->sheet;
    }

}
