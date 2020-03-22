<?php

namespace App\Domain;

abstract class Mapper {

    protected $db;
    protected $translation;
    // filter by specific user
    protected $select_results_of_user_only = true;
    protected $insert_user = true;
    protected $user_id;
    // Table prefix and name
    protected $table_prefix = '';
    protected $table = '';
    // Primary Key Name
    protected $id = 'id';
    // Data Object
    protected $dataobject = \App\Domain\DataObject::class;
    // m:n relationship with an usertable
    protected $has_user_table = false;
    protected $user_table = "";
    protected $element_name = "";

    public function __construct(\PDO $db, \App\Domain\Main\Translator $translation, \App\Domain\Base\CurrentUser $user) {
        $this->db = $db;
        $this->translation = $translation;

        // set ID of current user
        $this->user_id = $user && $user->getUser() ? $user->getUser()->id : null;
    }

    protected function getTableName($table = null) {
        if (is_null($table)) {
            $table = $this->table;
        }
        return $this->table_prefix . $table;
    }

    public function insert(DataObject $data) {

        // remove the user field from the dataobject, if the user should not be inserted
        $remove_user = !$this->insert_user;

        $data_array = $data->get_fields($remove_user, true, false);

        $sql = "INSERT INTO " . $this->getTableName() . " "
                . "        (" . implode(", ", array_keys($data_array)) . ") "
                . "VALUES  (" . implode(", ", ( array_map(function($row) {
                            return ":" . $row;
                        }, array_keys($data_array)))) .
                "           )";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getAll($sorted = false, $limit = false) {
        $sql = "SELECT * FROM " . $this->getTableName();

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

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
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

    public function get($id, $filtered = true, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE  {$whereID} = :id";

        $bindings = array("id" => $id);
        if ($filtered) {
            $this->addSelectFilterForUser($sql, $bindings);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function update(DataObject $data, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $bindings = $data->get_fields(!$this->insert_user, false, true);

        $parts = array();
        foreach (array_keys($bindings) as $row) {
            array_push($parts, " " . $row . " = :" . $row . "");
        }
        $sql = "UPDATE " . $this->getTableName() . " SET " . implode(", ", $parts) . " WHERE {$whereID} = :{$whereID}";

        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }

    public function delete($id, $parameter = null) {

        // possibilty do define another parameter
        $whereID = !is_null($parameter) ? $parameter : $this->id;

        $sql = "DELETE FROM " . $this->getTableName() . "  WHERE {$whereID} = :id";

        $bindings = array("id" => $id);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

    public function deleteAll() {
        $sql = "DELETE FROM " . $this->getTableName() . "";

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return $stmt->rowCount() > 0;
    }

    public function count() {
        $sql = "SELECT COUNT({$this->id}) FROM " . $this->getTableName();

        $bindings = array();
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    protected function addSelectFilterForUser(&$sql, &$bindings, $alias = "") {

        if ($this->select_results_of_user_only && !is_null($this->user_id)) {

            if (strpos(strtolower($sql), strtolower('WHERE')) !== false) {
                $sql .= " AND ";
            } else {
                $sql .= " WHERE ";
            }

            $sql .= " ({$alias}user = :user OR {$alias}user IS NULL) ";

            $bindings["user"] = $this->user_id;
        }
    }

    protected function isUsersDataset($data, $key = "user") {
        if ($this->select_results_of_user_only && !is_null($this->user_id)) {
            if ($data[$key] == $this->user_id) {
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
        $this->has_user_table = true;
        $this->user_table = $table;
        $this->element_name = $element_name;
    }

    public function hasUserTable() {
        return $this->has_user_table;
    }

    public function deleteUsers($element) {
        if ($this->has_user_table) {
            $sql = "DELETE FROM " . $this->getTableName($this->user_table) . "  WHERE {$this->element_name} = :element";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "element" => $element,
            ]);
            if (!$result) {
                throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
            }
            return true;
        }
    }

    public function addUsers($element, $users = array()) {
        if ($this->has_user_table && !empty($users)) {
            $data_array = array();
            $keys_array = array();
            foreach ($users as $idx => $user) {
                $data_array["element" . $idx] = $element;
                $data_array["user" . $idx] = $user;
                $keys_array[] = "(:element" . $idx . " , :user" . $idx . ")";
            }

            $sql = "INSERT INTO " . $this->getTableName($this->user_table) . " ({$this->element_name}, user) "
                    . "VALUES " . implode(", ", $keys_array) . "";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($data_array);

            if (!$result) {
                throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
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
        if ($this->has_user_table) {
            $table = $this->user_table;
            $element = $this->element_name;
        } elseif ($this->insert_user) {
            $table = $this->table;
            $element = $this->id;
        }

        if (!is_null($table)) {
            $sql = "SELECT user FROM " . $this->getTableName($table) . " WHERE {$element} = :id";

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
        $sql = "SELECT {$this->element_name} FROM " . $this->getTableName($this->user_table) . " WHERE user = :id";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    private function getUserItemsSQL($select = "DISTINCT t.*") {
        $sql = "SELECT {$select} FROM " . $this->getTableName() . " t LEFT JOIN " . $this->getTableName($this->user_table) . " tu ";
        $sql .= " ON t.id = tu.{$this->element_name} ";
        $sql .= " WHERE tu.user = :user OR t.user = :user";

        return $sql;
    }

    public function getUserItems($sorted = false, $limit = false, $user_id = null) {
        $sql = $this->getUserItemsSQL();

        $bindings = array("user" => $this->user_id);
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
            $results[] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getCountElementsOfUser($user_id = null) {
        $sql = $this->getUserItemsSQL("COUNT(DISTINCT t.id)");

        $bindings = array("user" => $this->user_id);
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
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE  hash = :hash";

        $bindings = array("hash" => $hash);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function setHash($id, $hash) {
        $sql = "UPDATE " . $this->getTableName() . " SET hash  = :hash WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'hash' => $hash,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function setUser($user_id) {
        $this->user_id = $user_id;
    }

    public function setSelectFilterForUser(\App\Domain\User\User $user = null) {
        if (!is_null($user)) {
            $this->select_results_of_user_only = true;
            $this->user_id = $user->id;
        } else {
            $this->select_results_of_user_only = false;
            $this->user_id = null;
        }
    }

    public function getMinMaxDate($min = 'date', $max = 'date') {
        $sql = "SELECT DATE(MIN($min)) as min, DATE(MAX($max)) as max FROM " . $this->getTableName() . "";

        $bindings = [];
        $this->addSelectFilterForUser($sql, $bindings);

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $result = ["min" => date('Y-m-d'), "max" => date('Y-m-d')];
        if ($stmt->rowCount() === 1) {
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        }
        return $result;
    }
    
    public function getDataObject(){
        return $this->dataobject;
    }

}
