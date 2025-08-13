<?php

namespace App\Domain\Settings;

class SettingsMapper extends \App\Domain\Mapper {

    protected $table = "global_settings";
    protected $dataobject = \App\Domain\Settings\Setting::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function exists() {
        $sql = "SHOW TABLES LIKE :table";

        $bindings = array("table" => $this->getTableName());

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getSetting($name, $reference = null) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE name = :name";
        $bindings = array("name" => $name);

        if (!is_null($reference)) {
            $sql .= " AND reference = :reference";
            $bindings["reference"] = $reference;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function updateLastRun($name, $reference = null) {

        $sql = "UPDATE " . $this->getTableName() . " SET value = UNIX_TIMESTAMP(), changedOn = CURRENT_TIMESTAMP WHERE name = :name";

        $bindings = array("name" => $name);

        if (!is_null($reference)) {
            $sql .= " AND reference = :reference ";
            $bindings["reference"] = $reference;
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function updateSetting($name, $value, $reference = null) {

        $sql = "UPDATE " . $this->getTableName() . " SET value = :value, changedOn = CURRENT_TIMESTAMP WHERE name = :name";

        $bindings = array("name" => $name, "value" => $value);

        if (!is_null($reference)) {
            $sql .= " AND reference = :reference ";
            $bindings["reference"] = $reference;
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function addSetting($name, $value, $type, $reference = null) {
        $insert = array(
            "name" => $name,
            "value" => $value,
            "type" => $type,
            "reference" => $reference
        );

        $sql = "INSERT INTO " . $this->getTableName() . " (name, value, type, reference) VALUES (:name, :value, :type, :reference)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($insert);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
        return $this->db->lastInsertId();
    }

    public function addOrUpdateSetting($name, $value, $type, $reference = null) {

        $sql = "SELECT id FROM " . $this->getTableName() . " WHERE name = :name ";
        $bindings = ["name" => $name];

        if (!is_null($reference)) {
            $sql .= " AND reference = :reference";
            $bindings["reference"] = $reference;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        // no entry present, so create one
        if ($stmt->rowCount() > 0) {
            return $this->updateSetting($name, $value, $reference);
        } else {
            return $this->addSetting($name, $value, $type, $reference);
        }
    }
}
