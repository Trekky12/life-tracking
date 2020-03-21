<?php

namespace App\Crawler\CrawlerHeader;

class Mapper extends \App\Base\Mapper {

    protected $table = "crawlers_headers";
    protected $dataobject = \App\Crawler\CrawlerHeader\CrawlerHeader::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getFromCrawler($id, $order = 'position', $hide_diff = false) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE crawler = :id ";

        $bindings = array("id" => $id);

        if ($hide_diff) {
            $sql .= " AND (diff = :diff OR diff IS NULL) ";
            $bindings["diff"] = 0;
        }

        if (!is_null($order)) {
            $sql .= " ORDER BY {$order}";
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

    public function unset_sort($header, $crawler) {
        $sql = "UPDATE " . $this->getTableName() . " SET sort = :sort WHERE id != :id and crawler = :crawler";
        $bindings = array("id" => $header, "sort" => null, "crawler" => $crawler);
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getInitialSortColumn($crawler) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE crawler = :crawler AND sort IS NOT NULL AND sortable = :sortable LIMIT 1";

        $bindings = array("crawler" => $crawler, "sortable" => 1);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

}
