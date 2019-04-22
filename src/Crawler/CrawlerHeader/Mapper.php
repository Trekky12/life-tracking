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

    public function unset_sort($header) {
        $sql = "UPDATE " . $this->getTable() . " SET sort = :sort WHERE id != :id";
        $bindings = array("id" => $header, "sort" => null);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
     public function getInitialSortColumn($crawler) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE crawler = :crawler AND sort IS NOT NULL AND sortable = :sortable LIMIT 1";

        $bindings = array("crawler" => $crawler, "sortable" => 1);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        
        if ($stmt->rowCount() > 0) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }

}
