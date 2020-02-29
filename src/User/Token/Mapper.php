<?php

namespace App\User\Token;

class Mapper extends \App\Base\Mapper {

    protected $table = 'global_tokens';
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function addToken($user_id, $token, $ip = null, $agent = null) {
        $sql = "INSERT INTO " . $this->getTableName() . "(user, token, ip, agent, changedOn) VALUES (:user, :token, :ip, :agent, :changedOn)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "user" => $user_id,
            "token" => $token,
            "ip" => $ip,
            "agent" => $agent,
            "changedOn" => date('Y-m-d H:i:s')
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
    }

    public function deleteToken($token) {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE token = :token";

        $bindings = array("token" => $token);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

    public function getUserFromToken($token) {
        $sql = "SELECT user FROM " . $this->getTableName() . " WHERE token = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["token" => $token]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        } else {
            throw new \Exception($this->translation->getTranslatedString('TOKEN_INVALID'), 404);
        }
    }

    public function updateTokenData($token, $ip = null, $agent = null) {
        $sql = "UPDATE " . $this->getTableName() . " SET changedOn =:changedOn, ip=:ip, agent=:agent WHERE token=:token";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "changedOn" => date('Y-m-d H:i:s'),
            "ip" => $ip,
            "agent" => $agent,
            "token" => $token
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function deleteOldTokens($month = 6) {
        $sql = "DELETE FROM " . $this->getTableName() . " WHERE DATEDIFF(NOW(), DATE_ADD(changedOn, INTERVAL :month MONTH)) >= 0";

        $bindings = ["month" => $month];

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount();
    }

}
