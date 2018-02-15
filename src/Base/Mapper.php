<?php

namespace App\Base;

use Interop\Container\ContainerInterface;

class Mapper {

    protected $db;
    protected $userid;
    protected $filterByUser = true;
    protected $table_prefix = '';
    protected $table = '';
    protected $id = "id";
    protected $model = '\App\Base\Model';

    public function __construct(ContainerInterface $ci, $table = null, $model = null, $filterByUser = null) {
        $this->ci = $ci;
        $this->db = $this->ci->get('db');
        $this->table = !is_null($table) ? $table : $this->table;
        $this->model = !is_null($model) ? $model : $this->model;
        $this->filterByUser = !is_null($filterByUser) ? $filterByUser : $this->filterByUser;

        if ($this->filterByUser) {
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

        $data_array = $data->get_fields(!$this->filterByUser);

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

    public function get($id) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE  {$this->id} = :id";

        $bindings = array("id" => $id);
        $this->filterByUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        } else {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
        }
    }

    public function update(Model $data) {

        $data_array = $data->get_fields(!$this->filterByUser);

        $parts = array();
        foreach (array_keys($data_array) as $row) {
            array_push($parts, " " . $row . " = :" . $row . "");
        }
        $sql = "UPDATE " . $this->getTable() . " SET " . implode(", ", $parts) . " WHERE {$this->id} = :{$this->id}";


        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM " . $this->getTable() . "  WHERE {$this->id} = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "id" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('DELETE_FAILED'));
        } else {
            return true;
        }
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

    public function dataTable($where, $bindings, $order, $limit) {
        $sql = "SELECT * FROM " . $this->getTable() . " ";

        if (!empty($where)) {
            $sql .= " {$where}";
        }

        $this->filterByUser($sql, $bindings, true);


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
        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new $this->model($row);
        }
        return $results;
    }

    public function dataTableCount($where, $bindings) {
        $sql = "SELECT COUNT({$this->id}) FROM " . $this->getTable() . " ";
        if (!empty($where)) {
            $sql .= "{$where}";
        }

        $this->filterByUser($sql, $bindings, true);

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

    protected function filterByUser(&$sql, &$bindings, $datatable = false, $alias = "") {

        if ($this->filterByUser && !is_null($this->userid)) {

            if (strpos(strtolower($sql), strtolower('WHERE')) !== false) {
                $sql .= " AND ";
            } else {
                $sql .= " WHERE ";
            }

            $sql .= " ({$alias}user = :user OR {$alias}user IS NULL) ";

            if ($datatable) {
                array_push($bindings, array("key" => "user", "val" => $this->userid, "type" => \PDO::PARAM_INT));
            } else {
                $bindings["user"] = $this->userid;
            }
        }
    }

}
