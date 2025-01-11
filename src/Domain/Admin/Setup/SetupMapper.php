<?php

namespace App\Domain\Admin\Setup;

class SetupMapper {

    protected $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function runMigration($sql) {

        $ret = $this->db->exec($sql);

        return ($ret !== false);
    }
}
