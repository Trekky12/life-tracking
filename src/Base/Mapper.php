<?php

namespace App\Base;

use Interop\Container\ContainerInterface;

abstract class Mapper {

    protected $db;
    
    // filter by specific user
    protected $filterByUser = true;
    protected $insertUser = true;
    protected $userid;
    
    // Table prefix and name
    protected $table_prefix = '';
    protected $table = '';
    // Primary Key Name
    protected $id = "id";
    
    // Modell
    protected $model = '\App\Base\Model';
    
    // m:n relationship with an usertable
    protected $hasUserTable = false;
    protected $user_table = "";
    protected $element_name = "";

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
        $this->db = $this->ci->get('db');

        // set ID of current user
        if ($this->filterByUser || $this->insertUser) {
            $currentUser = $this->ci->get('helper')->getUser();
            $this->userid = $currentUser ? $currentUser->id : null;
        }
    }

    protected function getTable($table = null) {
        if (is_null($table)) {
            $table = $this->table;
        }
        return $this->table_prefix . $table;
    }

    public function insert(Model $data, $removeUserParam = null) {

        $removeUser = !is_null($removeUserParam) && is_bool($removeUserParam) ? $removeUserParam : !$this->insertUser;

        $data_array = $data->get_fields($removeUser, true);

        $sql = "INSERT INTO " . $this->getTable() . " "
                . "        (" . implode(", ", array_keys($data_array)) . ") "
                . "VALUES  (" . implode(", ", ( array_map(function($row) {
                            return ":" . $row;
                        }, array_keys($data_array)))) .
                "           )";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getAll($sorted = false, $limit = false) {
        $sql = "SELECT * FROM " . $this->getTable();

        $bindings = array();
        $this->filterByUser($sql, $bindings);

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

    public function get($id, $filtered = true, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $sql = "SELECT * FROM " . $this->getTable() . " WHERE  {$whereID} = :id";

        $bindings = array("id" => $id);
        if ($filtered) {
            $this->filterByUser($sql, $bindings);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function update(Model $data, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $bindings = $data->get_fields(!$this->insertUser, false);

        $parts = array();
        foreach (array_keys($bindings) as $row) {
            array_push($parts, " " . $row . " = :" . $row . "");
        }
        $sql = "UPDATE " . $this->getTable() . " SET " . implode(", ", $parts) . " WHERE {$whereID} = :{$whereID}";

        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }

    public function delete($id, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $sql = "DELETE FROM " . $this->getTable() . "  WHERE {$whereID} = :id";

        $bindings = array("id" => $id);
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

    public function deleteAll() {
        $sql = "DELETE FROM " . $this->getTable() . "";

        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

    public function count() {
        $sql = "SELECT COUNT({$this->id}) FROM " . $this->getTable();

        $bindings = array();
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_DATA'));
    }

    protected function filterByUser(&$sql, &$bindings, $alias = "") {

        if ($this->filterByUser && !is_null($this->userid)) {

            if (strpos(strtolower($sql), strtolower('WHERE')) !== false) {
                $sql .= " AND ";
            } else {
                $sql .= " WHERE ";
            }

            $sql .= " ({$alias}user = :user OR {$alias}user IS NULL) ";

            $bindings["user"] = $this->userid;
        }
    }

    protected function isUsersDataset($data, $key = "user") {
        if ($this->filterByUser && !is_null($this->userid)) {
            if ($data[$key] == $this->userid) {
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * ===========================================================================
     * m-n table with user
     * ===========================================================================
     */
    public function setUserTable($table, $element_name) {
        $this->hasUserTable = true;
        $this->user_table = $table;
        $this->element_name = $element_name;
    }

    public function hasUserTable() {
        return $this->hasUserTable;
    }

    public function deleteUsers($element) {
        if ($this->hasUserTable) {
            $sql = "DELETE FROM " . $this->getTable($this->user_table) . "  WHERE {$this->element_name} = :element";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "element" => $element,
            ]);
            if (!$result) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
            }
            return true;
        }
    }

    public function addUsers($element, $users = array()) {
        if ($this->hasUserTable && !empty($users)) {
            $data_array = array();
            $keys_array = array();
            foreach ($users as $idx => $user) {
                $data_array["element" . $idx] = $element;
                $data_array["user" . $idx] = $user;
                $keys_array[] = "(:element" . $idx . " , :user" . $idx . ")";
            }

            $sql = "INSERT INTO " . $this->getTable($this->user_table) . " ({$this->element_name}, user) "
                    . "VALUES " . implode(", ", $keys_array) . "";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data_array);

            if (!$result) {
                throw new \Exception($this->ci->get('helper')->getTranslatedString('SAVE_NOT_POSSIBLE'));
            } else {
                return $this->db->lastInsertId();
            }
        }
    }

    /**
     * Get Users of dataset
     * either from user table or the user element of the dataset (owner)
     * @param type $id
     * @return type
     */
    public function getUsers($id) {
        $table = null;
        $element = null;
        if ($this->hasUserTable) {
            $table = $this->user_table;
            $element = $this->element_name;
        } elseif ($this->insertUser) {
            $table = $this->table;
            $element = $this->id;
        }

        if (!is_null($table)) {
            $sql = "SELECT user FROM " . $this->getTable($table) . " WHERE {$element} = :id";

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

    public function getElementsOfUser($id) {
        $sql = "SELECT {$this->element_name} FROM " . $this->getTable($this->user_table) . " WHERE user = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }
    
    private function getUserItemsSQL($select = "DISTINCT t.*"){
        $sql = "SELECT {$select} FROM " . $this->getTable() . " t LEFT JOIN " . $this->getTable($this->user_table) . " tu ";
        $sql .= " ON t.id = tu.{$this->element_name} ";
        $sql .= " WHERE tu.user = :user OR t.user = :user";
        
        return $sql;
    }

    public function getUserItems($sorted = false, $limit = false, $user_id = null) {
        $sql = $this->getUserItemsSQL();

        $bindings = array("user" => $this->userid);
        if (!is_null($user_id)) {
            $bindings["user"] = $user_id;
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
            $results[] = new $this->model($row);
        }
        return $results;
    }

    public function getCountElementsOfUser($user_id = null) {
        $sql = $this->getUserItemsSQL("COUNT(DISTINCT t.id)");

        $bindings = array("user" => $this->userid);
        if (!is_null($user_id)) {
            $bindings["user"] = $user_id;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

    public function getFromHash($hash) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE  hash = :hash";

        $bindings = array("hash" => $hash);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function setHash($id, $hash) {
        $sql = "UPDATE " . $this->getTable() . " SET hash  = :hash WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'hash' => $hash,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function setUser($user_id) {
        $this->userid = $user_id;
    }

    public function setFilterByUser($filter_by_user) {
        $this->filterByUser = $filter_by_user;
        if ($this->filterByUser) {
            $currentUser = $this->ci->get('helper')->getUser();
            $this->userid = $currentUser ? $currentUser->id : null;
        }
    }

    public function getMinMaxDate($min = 'date', $max = 'date') {
        $sql = "SELECT DATE(MIN($min)) as min, DATE(MAX($max)) as max FROM " . $this->getTable() . "";

        $bindings = [];
        $this->filterByUser($sql, $bindings);

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $result = ["min" => date('Y-m-d'), "max" => date('Y-m-d')];
        if ($stmt->rowCount() === 1) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return $result;
    }

}
