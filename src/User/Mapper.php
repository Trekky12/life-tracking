<?php

namespace App\User;

class Mapper extends \App\Base\Mapper {
    
    protected $table = 'users';
    protected $model = '\App\User\User';
    protected $filterByUser = false;

    public function getUserFromLogin($login) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE login = :login ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["login" => $login]);

        if ($stmt->rowCount() > 0) {
            return new \App\User\User($stmt->fetch());
        } else {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
        }
    }

    public function update_password($id, $new_password) {
        $sql = "UPDATE " . $this->getTable() . " SET password=:password WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "password" => $new_password,
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
        if ($stmt->rowCount() === 0) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'), 404);
        }
    }

}
