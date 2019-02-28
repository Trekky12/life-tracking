<?php

namespace App\Notifications;

class Mapper extends \App\Base\Mapper {

    protected $table = 'notifications';
    protected $model = '\App\Notifications\Notification';
    protected $filterByUser = false;
    protected $insertUser = false;

    public function getNotificationsByClient($client, $limit = 2, $offset = 0) {
        $sql = "SELECT * FROM " . $this->getTable() . "  WHERE client = :client ORDER BY id DESC LIMIT {$offset},{$limit}";

        $bindings = array("client" => $client);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[] = new $this->model($row);
        }
        return $results;
    }
    
    public function getNotificationsCountByClient($client) {
        $sql = "SELECT COUNT(*) FROM " . $this->getTable() . "  WHERE client = :client";

        $bindings = array("client" => $client);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }
    
    public function markAsSeen($notifications){
        $sql = "UPDATE " . $this->getTable() . " SET seen = CURRENT_TIMESTAMP WHERE id in (" . implode(',', $notifications) . ")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if (!$result) {
            throw new \Exception($this->ci->get('helper')->getTranslatedString('UPDATE_FAILED'));
        }
    }
    
    public function getUnreadNotificationsCountByClient($client) {
        $sql = "SELECT COUNT(*) FROM " . $this->getTable() . "  WHERE client = :client AND seen IS NULL";

        $bindings = array("client" => $client);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

}
