<?php

namespace App\Base;

use Interop\Container\ContainerInterface;

abstract class Mapper {

    protected $db;
    protected $userid;
    protected $filterByUser = true;
    protected $insertUser = true;
    protected $table_prefix = '';
    protected $table = '';
    protected $id = "id";
    protected $model = '\App\Base\Model';
    protected $hasUserTable = false;
    protected $user_table = "";
    protected $element_name = "";

    public function __construct(ContainerInterface $ci, $table = null, $model = null, $filterByUser = null, $insertUser = null) {
        $this->ci = $ci;
        $this->db = $this->ci->get('db');
        $this->table = !is_null($table) ? $table : $this->table;
        $this->model = !is_null($model) ? $model : $this->model;
        $this->filterByUser = !is_null($filterByUser) ? $filterByUser : $this->filterByUser;
        $this->insertUser = !is_null($insertUser) ? $insertUser : $this->insertUser;

        if ($this->filterByUser || $this->insertUser) {
            $user = $this->ci->get('helper')->getUser();
            $this->userid = $user ? $user->id : null;
        }
    }

    protected function getTable($table = null) {
        if (is_null($table)) {
            $table = $this->table;
        }
        return $this->table_prefix . $table;
    }

    public function insert(Model $data) {

        $data_array = $data->get_fields(!$this->insertUser);

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

        $bindings = $data->get_fields(!$this->insertUser);

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
        if ($this->hasUserTable) {
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

    public function getUsers($id) {
        if ($this->hasUserTable) {
            $sql = "SELECT user FROM " . $this->getTable($this->user_table) . " WHERE {$this->element_name} = :id";

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
    
    public function getUserItems($sorted = false, $limit = false) {
        $sql = "SELECT t.* FROM " . $this->getTable() . " t LEFT JOIN " . $this->getTable($this->user_table) . " tu ";
        $sql .= " ON t.id = tu.{$this->element_name} ";
        $sql .= " WHERE tu.user = :user OR t.user = :user";

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
}
