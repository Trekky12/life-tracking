<?php

namespace App\Board;

class LabelMapper extends \App\Base\Mapper {

    protected $table = "labels";
    protected $model = "\App\Board\Label";
    protected $filterByUser = false;

    public function getLabelsFromBoard($board) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE board = :board ORDER BY dt";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "board" => $board
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

}
