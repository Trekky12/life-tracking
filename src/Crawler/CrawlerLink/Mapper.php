<?php

namespace App\Crawler\CrawlerLink;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers_links";
    protected $dataobject = \App\Crawler\CrawlerLink\CrawlerLink::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getFromCrawler($id, $order = null) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE crawler = :id ";

        if (!is_null($order)) {
            $sql .= " ORDER BY {$order}";
        }

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->dataobject($row);
        }
        return $results;
    }

}
