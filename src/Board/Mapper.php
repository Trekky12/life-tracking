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
