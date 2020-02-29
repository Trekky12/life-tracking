<?php

namespace App\Banlist;

class Mapper extends \App\Base\Mapper {

    protected $table = "global_banlist";
    protected $model = "\App\Base\Model";
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getBlockedIPAdresses($attempts = 2) {
        $sql = "SELECT COUNT(ip) as attempts, createdOn, ip, username, changedOn FROM " . $this->getTableName() . " GROUP BY ip HAVING COUNT(ip) > :attempts ";

        $bindings = array("attempts" => $attempts);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function getFailedLoginAttempts($ip) {
        $sql = "SELECT COUNT(ip) FROM " . $this->getTableName() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

    public function deleteFailedLoginAttempts($ip) {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

}
