<?php

namespace App\Board;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards";
    protected $model = "\App\Board\Board";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "boards_user";
    protected $element_name = "board";

    public function getUserStacks($id){
        $sql = "SELECT st.id FROM " . $this->getTableName($this->user_table) . " ub, " . $this->getTableName("boards_stacks") . " st "
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
        $sql = "SELECT ca.id FROM " . $this->getTableName($this->user_table) . " ub, " . $this->getTableName("boards_stacks") . " st, " . $this->getTableName("boards_cards") . " ca "
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
