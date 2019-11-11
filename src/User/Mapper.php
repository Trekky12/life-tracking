<?php

namespace App\User;

class Mapper extends \App\Base\Mapper {

    protected $table = 'global_users';
    protected $model = '\App\User\User';
    protected $filterByUser = false;
    protected $insertUser = false;

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
        $sql = "UPDATE " . $this->getTable() . " SET password=:password, force_pw_change =:force_pw_change WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "password" => $new_password,
            "id" => $id,
            "force_pw_change" => 0
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
        if ($stmt->rowCount() === 0) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'), 404);
        }
    }

    public function update_image($id, $image) {
        $sql = "UPDATE " . $this->getTable() . " SET image=:image WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "image" => $image,
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function update_profile($id, \App\User\User $user) {
        $data = $user->get_fields();

        $bindings = ["id" => $id];
        $parts = array();
        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                $parts[] = " " . $key . " = :" . $key . "";
                $bindings[$key] = $value;
            }
        }
        $sql = "UPDATE " . $this->getTable() . " SET " . implode(", ", $parts) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }

}
