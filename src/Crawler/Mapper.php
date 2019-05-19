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

    public function getVisibleCrawlers($sorted = false, $limit = false) {
        $sql = "SELECT c.* FROM " . $this->getTable() . " c LEFT JOIN " . $this->getTable($this->user_table) . " cu ";
        $sql .= " ON c.id = cu.crawler ";
        $sql .= " WHERE cu.user = :user OR c.user = :user";

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

}
