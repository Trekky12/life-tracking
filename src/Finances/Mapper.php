<?php

namespace App\Finances;

class Mapper extends \App\Base\Mapper {
    
    protected $table = 'finances';
    protected $model = '\App\Finances\FinancesEntry';

    private function getSQL($select, $where) {
        $sql = "SELECT {$select} "
                . " FROM " . $this->getTable() . " f INNER JOIN " . $this->getTable('finances_categories') . " fc "
                . " ON f.category = fc.id";


        if (!empty($where)) {
            $sql .= " {$where}";

            /**
             * Replace ambigious columns id with correct table
             */
            $sql = str_replace('`id`', 'f.`id`', $sql);

            /**
             * Replace category index with name in category table
             */
            $sql = str_replace('`category`', 'fc.`name`', $sql);
        }


        return $sql;
    }

    public function dataTable($where, $bindings, $order, $limit) {

        $sql = $this->getSQL("f.id as id, f.date, f.time, f.description, f.value, f.type, fc.name as category", $where);
        $this->filterByUser($sql, $bindings, true, "f.");

        if (!empty($order)) {
            $sql .= " {$order}";
        }
        if (!empty($limit)) {
            $sql .= " {$limit}";
        }

        $stmt = $this->db->prepare($sql);

        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function dataTableCount($where, $bindings) {
        $sql = $this->getSQL("COUNT(f.id)", $where);
        $this->filterByUser($sql, $bindings, true, "f.");

        $stmt = $this->db->prepare($sql);
        if (is_array($bindings)) {
            for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
                $binding = $bindings[$i];
                $stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
            }
        }

        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_DATA'));
    }

    public function statsTotal() {
        $sql = "SELECT YEAR(date) as year, type,  SUM(value) as sum, COUNT(value) as count FROM " . $this->getTable();

        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $sql .= " GROUP BY YEAR(date), type"
                . " ORDER BY YEAR(date) DESC";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsYear($year) {
        $sql = "SELECT YEAR(date) as year, MONTH(date) as month, type,  SUM(value) as sum, COUNT(value) as count "
                . "FROM " . $this->getTable() . " "
                . "WHERE YEAR(date) = :year ";

        $bindings = array("year" => $year);
        $this->filterByUser($sql, $bindings);

        $sql .= " GROUP BY YEAR(date), MONTH(date), type"
                . " ORDER BY YEAR(date) DESC, MONTH(date) DESC";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMonthType($year, $month, $type) {

        $sql = "SELECT YEAR(date) as year, MONTH(date) as month, type, fc.name as category, SUM(value) as sum, COUNT(value) as count, f.category as category_id "
                . "FROM " . $this->getTable() . " f, " . $this->getTable("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . "AND MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type ";

        $bindings = array("year" => $year, "month" => $month, "type" => $type);
        $this->filterByUser($sql, $bindings, false, "f.");

        $sql .= " GROUP BY YEAR(date), MONTH(date), type, category";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMonthCategory($year, $month, $type, $category) {

        $sql = "SELECT id, type, description, value FROM " . $this->getTable() . " "
                . "WHERE category = :category "
                . "AND MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type ";

        $bindings = array("year" => $year, "month" => $month, "type" => $type, "category" => $category);
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    public function statsMailBalance($user, $month, $year, $type) {
        $sql = "SELECT SUM(value) FROM " . $this->getTable() . " "
                . "WHERE MONTH(date) = :month "
                . "AND YEAR(date) = :year "
                . "AND type = :type "
                . "AND user = :user "
                . "GROUP BY type";

        $bindings = array("year" => $year, "month" => $month, "type" => $type, "user" => $user);
                

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return floatval($stmt->fetchColumn());
    }
    
    public function statsMailExpenses($user, $month, $year, $limit = 5) {
        $sql = "SELECT f.date, f.description, fc.name as category, f.value "
                . "FROM " . $this->getTable() . " f, " . $this->getTable("finances_categories") . " fc "
                . "WHERE f.category = fc.id "
                . "AND MONTH(f.date) = :month "
                . "AND YEAR(f.date) = :year "
                . "AND f.type = :type "
                . "AND f.user = :user ";
        
        $bindings = array("year" => $year, "month" => $month, "type" => 0, "user" => $user);        
        $sql .= "ORDER BY value desc LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }

    /**
     * not used
     */
    public function statsCategory() {
        $sql = "SELECT YEAR(date) as year, type, fc.name as category, SUM(value) as sum, COUNT(value) as count FROM " . $this->getTable() . " f, " . $this->getTable("finances_categories") . " fc WHERE f.category = fc.id ";

        $bindings = array("year" => $year, "month" => $month);
        $this->filterByUser($sql, $bindings);

        $sql .= " GROUP BY YEAR(date), type, category";


        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }
    
    public function set_category($id, $category) {
        $sql = "UPDATE " . $this->getTable() . " SET category = :category WHERE id  = :id";
        $bindings = array("id" => $id, "category" => $category);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
