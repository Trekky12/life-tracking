<?php

namespace App\Board\Stack;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards_stacks";
    protected $model = "\App\Board\Stack\Stack";
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getStacksFromBoard($board, $archive = 0) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE board = :board ";

        $bindings = ["board" => $board];

        if ($archive == 0) {
            $sql .= " AND archive = :archive ";
            $bindings["archive"] = $archive;
        }

        $sql .= "ORDER BY position, changedOn";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function updatePosition($id, $position, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET position=:position, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "position" => $position,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function setArchive($id, $archive, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET archive=:archive, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "archive" => $archive,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return true;
    }

}
