<?php

namespace App\Board;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards";
    protected $model = "\App\Board\Board";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "boards_user";
    protected $element_name = "board";

    public function getVisibleBoards($sorted = false, $limit = false) {
        $sql = "SELECT b.* FROM " . $this->getTable() . " b LEFT JOIN " . $this->getTable($this->user_table) . " bu ";
        $sql .= " ON b.id = bu.board ";
        $sql .= "WHERE bu.user = :user OR b.user = :user";

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
        $sql = "SELECT st.id FROM " . $this->getTable($this->user_table) . " ub, " . $this->getTable("boards_stacks") . " st "
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
    
    public function getUserCards($id){
        $sql = "SELECT ca.id FROM " . $this->getTable($this->user_table) . " ub, " . $this->getTable("boards_stacks") . " st, " . $this->getTable("boards_cards") . " ca "
                . " WHERE ub.user = :id "
                . " AND st.board = ub.board "
                . " AND st.id = ca.stack";

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
