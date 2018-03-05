<?php

namespace App\Board;

class StackMapper extends \App\Base\Mapper {

    protected $table = "stacks";
    protected $model = "\App\Board\Stack";
    protected $filterByUser = false;

    public function getStacksFromBoard($board, $archive = 0) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE board = :board AND archive = :archive ORDER BY position, dt";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "board" => $board,
            "archive" => $archive
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

    public function setArchive($id, $archive) {
        $sql = "UPDATE " . $this->getTable() . " SET archive=:archive WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "archive" => $archive,
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
        return true;
    }

}
