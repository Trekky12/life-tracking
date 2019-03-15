<?php

namespace App\Crawler;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers";
    protected $model = "\App\Crawler\Crawler";
    protected $filterByUser = false;
    protected $insertUser = true;
    protected $hasUserTable = true;
    protected $user_table = "crawlers_user";
    protected $element_name = "crawler";

    public function getCrawlerFromHash($hash) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE  hash = :hash";

        $bindings = array("hash" => $hash);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

    public function getVisibleCrawlers($sorted = false, $limit = false) {
        $sql = "SELECT c.* FROM " . $this->getTable() . " c, " . $this->getTable($this->user_table) . " cu ";
        $sql .= "WHERE (c.id = cu.crawler AND cu.user = :user) OR c.user = :user";
        

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
