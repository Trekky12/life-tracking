<?php

namespace App\Car;

class Mapper extends \App\Base\Mapper {

    protected $table = "cars";
    protected $model = "\App\Car\Car";
    protected $filterByUser = false;

    public function deleteUserCar($user) {
        $sql = "DELETE FROM " . $this->getTable("user_cars") . "  WHERE user = :user";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "user" => $user,
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        } else {
            return true;
        }
    }

    public function addUserCar($user, $cars = array()) {

        $data_array = array();
        $keys_array = array();
        foreach ($cars as $idx => $car) {
            $data_array["user" . $idx] = $user;
            $data_array["car" . $idx] = $car;
            $keys_array[] = "(:user" . $idx . " , :car" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTable("user_cars") . " (user, car) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getUserCars($id) {
        $sql = "SELECT car FROM " . $this->getTable("user_cars") . " WHERE user = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

}
