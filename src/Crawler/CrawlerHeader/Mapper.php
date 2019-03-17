<?php

namespace App\Crawler\CrawlerHeader;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers_headers";
    protected $model = "\App\Crawler\CrawlerHeader\CrawlerHeader";
    protected $filterByUser = false;
    protected $insertUser = false;

    public function getFromCrawler($id, $order = null) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE crawler = :id ";
        
        if(!is_null($order)){
            $sql .= " ORDER BY {$order}";
        }

        $bindings = array("id" => $id);

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
