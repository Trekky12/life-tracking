<?php

namespace App\Domain\Crawler\CrawlerDataset;

class CrawlerDatasetMapper extends \App\Domain\Mapper {

    protected $table = "crawlers_dataset";
    protected $dataobject = \App\Domain\Crawler\CrawlerDataset\CrawlerDataset::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    private function getTableSQL($select, $filter = "changedOn") {
        $sql = "SELECT {$select} FROM " . $this->getTableName() . " "
                . " WHERE crawler = :crawler "
                . "   AND DATE({$filter}) >= :from "
                . "   AND DATE({$filter}) <= :to "
                . "AND LOWER(data) LIKE LOWER(:searchQuery) ";
        return $sql;
    }

    public function getCountFromCrawler($crawler, $from, $to, $filter = "changedOn", $searchQuery = "%") {
        $bindings = ["crawler" => $crawler, "from" => $from, "to" => $to, "searchQuery" => $searchQuery];
        $sql = $this->getTableSQL("COUNT(*)", $filter);

        $stmt = $this->db->prepare($sql);

        $stmt->execute($bindings);
        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getDataFromCrawler($crawler, $from, $to, $filter = "changedOn", $sortColumn = "changedOn", $sortDirection = "DESC", $limit = null, $start = 0, $searchQuery = '%') {

        $bindings = ["crawler" => $crawler, "from" => $from, "to" => $to, "searchQuery" => $searchQuery];

        $sql = $this->getTableSQL("*", $filter);

        $sql .= " ORDER BY {$sortColumn} {$sortDirection}, id {$sortDirection}";

        if (!is_null($limit)) {
            $sql .= " LIMIT {$start}, {$limit}";
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

    public function getIDFromIdentifier($crawler, $identifier) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE identifier = :identifier AND crawler = :crawler";

        $bindings = array("identifier" => $identifier, "crawler" => $crawler);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() == 1) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

}
