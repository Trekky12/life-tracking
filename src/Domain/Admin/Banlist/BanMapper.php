<?php

namespace App\Domain\Admin\Banlist;

class BanMapper extends \App\Domain\Mapper {

    protected $table = "global_banlist";
    protected $dataobject = \App\Domain\DataObject::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;
    protected $id = 'ip';

    public function getBlockedIPAdresses($attempts = 2, $with_username = true) {
        $sql = "SELECT COUNT(ip) as attempts, createdOn, ip, username, changedOn FROM " . $this->getTableName();

        if($with_username){
            $sql .= " WHERE username IS NOT NULL ";
        }else{
            $sql .= " WHERE createdOn >= NOW() - INTERVAL 5 MINUTE ";
        }

        $sql .= "GROUP BY ip HAVING COUNT(ip) > :attempts ";

        $bindings = array("attempts" => $attempts);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getEntries($ip, $with_username = true) {
        $sql = "SELECT COUNT(ip) FROM " . $this->getTableName() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        if($with_username){
            $sql .= " AND username IS NOT NULL ";
        }else{
            $sql .= " AND createdOn >= NOW() - INTERVAL 5 MINUTE ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }


    public function unBan($ip, $with_username = true) {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        if($with_username){
            $sql .= " AND username IS NOT NULL ";
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

}
