<?php

namespace App\Domain\Crawler;

class CrawlerMapper extends \App\Domain\Mapper {

    protected $table = "crawlers";
    protected $dataobject = \App\Domain\Crawler\Crawler::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;
    protected $has_user_table = true;
    protected $user_table = "crawlers_user";
    protected $element_name = "crawler";

    public function updateLastAccess($id, $user) {
        $sql = "UPDATE " . $this->getTableName($this->user_table) . " SET lastAccess = :lastAccess WHERE user = :user AND crawler = :crawler";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "lastAccess" => date('Y-m-d'),
            "user" => $user,
            "crawler" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getLastAccess($id, $user) {
        $sql = "SELECT lastAccess FROM " . $this->getTableName($this->user_table) . " WHERE user = :user AND crawler = :crawler";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "user" => $user,
            "crawler" => $id
        ]);

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchColumn();
        }
        return null;
    }

    public function setLastAccess($id, $user, $date) {
        $sql = "UPDATE " . $this->getTableName($this->user_table) . " SET lastAccess = :lastAccess WHERE user = :user AND crawler = :crawler";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "lastAccess" => $date,
            "user" => $user,
            "crawler" => $id
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

}
