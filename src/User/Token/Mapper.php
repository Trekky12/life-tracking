<?php

namespace App\User\Token;

class Mapper extends \App\Base\Mapper {

    protected $table = 'global_tokens';
    protected $filterByUser = false;
    protected $insertUser = false;

    public function addToken($user_id, $token, $ip = null, $agent = null) {
        $sql = "INSERT INTO " . $this->getTable() . "(user, token, ip, agent) VALUES (:user, :token, :ip, :agent)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "user" => $user_id,
            "token" => $token,
            "ip" => $ip,
            "agent" => $agent
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        }
    }
    
    public function deleteToken($token) {
        $sql = "DELETE FROM " . $this->getTable() . " WHERE token = :token";

        $bindings = array("token" => $token);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }
    
    public function getUserFromToken($token) {
        $sql = "SELECT user FROM " . $this->getTable() . " WHERE token = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["token" => $token]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        } else {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('TOKEN_INVALID'), 404);
        }
    }
    
    public function updateTokenData($token, $ip = null, $agent = null) {
        $sql = "UPDATE " . $this->getTable() . " SET changedOn =:changedOn, ip=:ip, agent=:agent WHERE token=:token";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "changedOn" => date('Y-m-d G:i:s'),
            "ip" => $ip,
            "agent" => $agent,
            "token" => $token
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
    public function deleteOldTokens($month = 6){
        $sql = "DELETE FROM " . $this->getTable() . " WHERE DATEDIFF(NOW(), DATE_ADD(changedOn, INTERVAL :month MONTH)) >= 0";

        $bindings = ["month" => $month];
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount();
    }

}
