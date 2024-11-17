<?php

namespace App\Domain\Timesheets\Project;

class ProjectMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_projects";
    protected $dataobject = \App\Domain\Timesheets\Project\Project::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "timesheets_projects_users";
    protected $element_name = "project";

    public function getEncryptionParameters($project_id) {
        $sql = "SELECT salt, iterations, masterKeyEncryptedWithKEK, testMessageEncryptedWithKEK, masterKeyEncryptedWithRecoveryKey, recoveryKeyEncryptedWithMasterKey, testMessageEncryptedWithRecoveryKey FROM " . $this->getTableName() . "  "
            . "WHERE id = :project ";

        $bindings = array("project" => $project_id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return null;
    }

    public function setEncryptionParameters($project_id, $salt, $iterations, $masterKeyEncryptedWithKEK, $testMessageEncryptedWithKEK, $masterKeyEncryptedWithRecoveryKey = null, $recoveryKeyEncryptedWithMasterKey = null, $testMessageEncryptedWithRecoveryKey = null) {

        $bindings = [
            "project" => $project_id,
            "salt" => $salt,
            "iterations" => $iterations,
            "masterKeyEncryptedWithKEK" => $masterKeyEncryptedWithKEK,
            "testMessageEncryptedWithKEK" => $testMessageEncryptedWithKEK
        ];

        $sql = "UPDATE " . $this->getTableName() . " ";
        $sql .= " SET salt = :salt, iterations = :iterations, masterKeyEncryptedWithKEK = :masterKeyEncryptedWithKEK, testMessageEncryptedWithKEK = :testMessageEncryptedWithKEK ";
           
        if(!is_null($masterKeyEncryptedWithRecoveryKey)){
            $bindings["masterKeyEncryptedWithRecoveryKey"] = $masterKeyEncryptedWithRecoveryKey;
            $sql .= ", masterKeyEncryptedWithRecoveryKey = :masterKeyEncryptedWithRecoveryKey ";
        }
        if(!is_null($recoveryKeyEncryptedWithMasterKey)){
            $bindings["recoveryKeyEncryptedWithMasterKey"] = $recoveryKeyEncryptedWithMasterKey;
            $sql .= ", recoveryKeyEncryptedWithMasterKey = :recoveryKeyEncryptedWithMasterKey ";
        }

        if(!is_null($testMessageEncryptedWithRecoveryKey)){
            $bindings["testMessageEncryptedWithRecoveryKey"] = $testMessageEncryptedWithRecoveryKey;
            $sql .= ", testMessageEncryptedWithRecoveryKey = :testMessageEncryptedWithRecoveryKey ";
        }

        $sql .=  " WHERE id = :project ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }
}
