<?php

namespace App\Settings;

class SettingsMapper extends \App\Base\Mapper {

    protected $table = "global_settings";
    protected $model = "\App\Settings\Setting";
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getSetting($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE name = :name";

        $bindings = array("name" => $name);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }

    public function updateLastRun($name) {

        $sql = "UPDATE " . $this->getTableName() . " SET value = UNIX_TIMESTAMP(), changedOn = CURRENT_TIMESTAMP WHERE name = :name";

        $bindings = array("name" => $name);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
