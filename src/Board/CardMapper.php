<?php

namespace App\Board;

class CardMapper extends \App\Base\Mapper {

    protected $table = "cards";
    protected $model = "\App\Board\Card";
    protected $filterByUser = false;

    public function getCardsFromStack($stack) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE stack = :stack ORDER BY position, dt";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "stack" => $stack
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function updatePosition($id, $position) {
        $sql = "UPDATE " . $this->getTable() . " SET position=:position WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "position" => $position,
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
