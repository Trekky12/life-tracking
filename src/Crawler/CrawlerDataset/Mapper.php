<?php

namespace App\Crawler\CrawlerDataset;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers_dataset";
    protected $model = "\App\Crawler\CrawlerDataset\CrawlerDataset";
    protected $filterByUser = false;
    protected $insertUser = false;

    private function getTableSQL($select, $filter = "changedOn") {
        $sql = "SELECT {$select} FROM " . $this->getTable() . " "
                . " WHERE crawler = :crawler "
                . "   AND DATE({$filter}) >= :from "
                . "   AND DATE({$filter}) <= :to "
                . "AND data LIKE :searchQuery ";
        return $sql;
    }

    public function getFromCrawler($crawler, $from, $to, $filter = "changedOn", $order = "changedOn DESC, id DESC", $limit = null) {

        $bindings = ["crawler" => $crawler, "from" => $from, "to" => $to, "searchQuery" => "%"];

        $sql = $this->getTableSQL("*", $filter);

        $sql .= "ORDER BY {$order}";

        if (!is_null($limit)) {
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

    public function getCountFromCrawler($crawler, $from, $to, $filter = "changedOn", $searchQuery = "") {
        $bindings = ["crawler" => $crawler, "from" => $from, "to" => $to, "searchQuery" => "%" . $searchQuery . "%"];
        $sql = $this->getTableSQL("COUNT(*)", $filter);

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->ci->get('helper')->getTranslatedString('NO_DATA'));
    }

    public function tableData($crawler, $from, $to, $filter, $start, $limit, $searchQuery, $sortColumn, $sortDirection) {

        $bindings = ["crawler" => $crawler, "from" => $from, "to" => $to, "searchQuery" => "%" . $searchQuery . "%"];

        $sql = $this->getTableSQL("*", $filter);

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, id {$sortDirection}";

        $sql .= " LIMIT {$start}, {$limit}";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function getIDFromIdentifier($crawler, $identifier) {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE identifier = :identifier AND crawler = :crawler";

        $bindings = array("identifier" => $identifier, "crawler" => $crawler);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() == 1) {
            return new $this->model($stmt->fetch());
        }
        return null;
    }

}
