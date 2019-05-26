<?php

namespace App\Finances\Category;

class Mapper extends \App\Base\Mapper {
    
    protected $table = 'finances_categories';
    protected $model = '\App\Finances\Category\Category';
    
    public function set_default($default) {
        $sql = "UPDATE " . $this->getTable() . " SET is_default = :is_default WHERE id = :id";
        $bindings = array("id" => $default, "is_default" => 1);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
    public function unset_default($default) {
        $sql = "UPDATE " . $this->getTable() . " SET is_default = :is_default WHERE id != :id";
        $bindings = array("id" => $default, "is_default" => 0);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
    public function get_default() {
        $sql = "SELECT id FROM " . $this->getTable() . " WHERE is_default = :is_default";
                
        $bindings = array("is_default" => 1);
        $this->filterByUser($sql, $bindings);
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }
    
    public function getDefaultofUser($user) {
        $bindings = ["is_default" => 1, "user" => $user];
        
        $sql = "SELECT id FROM " . $this->getTable() . " WHERE is_default = :is_default AND user =:user LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }
}
