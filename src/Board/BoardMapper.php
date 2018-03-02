<?php

namespace App\Board;

class BoardMapper extends \App\Base\Mapper {

    protected $table = "boards";
    protected $model = "\App\Board\Board";
    protected $filterByUser = true;
    protected $hasUserTable = true;
    protected $user_table = "boards_user";
    protected $element_name = "board";

    public function getBoardFromHash($hash) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE  hash = :hash";

        $bindings = array("hash" => $hash);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function getVisibleBoards($sorted = false, $limit = false) {
        $sql = "SELECT b.* FROM " . $this->getTable() . " b, " . $this->getTable("boards_user") . " bu ";
        $sql .= "WHERE b.id = bu.board AND bu.user = :user";
        

        $bindings = array();
        if (!is_null($this->userid)) {
            $bindings["user"] = $this->userid;
        }

        if ($sorted && !is_null($sorted)) {
            $sql .= " ORDER BY {$sorted}";
        }

        if ($limit && !is_null($limit)) {
            $sql .= " LIMIT {$limit}";
        }


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }
    
    public function getUserStacks($id){
        $sql = "SELECT st.id FROM " . $this->getTable($this->user_table) . " ub, " . $this->getTable("stacks") . " st "
                . " WHERE ub.user = :id "
                . " AND st.board = ub.board";

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
