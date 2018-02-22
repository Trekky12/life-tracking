<?php

namespace App\Main;

class BanlistMapper extends \App\Base\Mapper {

    protected $table = "banlist";
    protected $model = "\App\Base\Model";
    protected $filterByUser = false;

    public function getFailedLoginAttempts($ip) {
        $sql = "SELECT COUNT(ip) FROM " . $this->getTable() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

    public function deleteFailedLoginAttempts($ip) {
        $sql = "DELETE FROM " . $this->getTable() . " WHERE ip = :ip";

        $bindings = array("ip" => $ip);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

}
