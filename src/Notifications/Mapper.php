<?php

namespace App\Notifications;

class Mapper extends \App\Base\Mapper {

    protected $table = 'notifications';
    protected $model = '\App\Notifications\Notification';
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getNotificationsByUser($user, $limit = 2, $offset = 0) {
        $sql = "SELECT * FROM " . $this->getTableName() . "  WHERE user = :user ORDER BY id DESC LIMIT {$offset},{$limit}";

        $bindings = array("user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[] = new $this->model($row);
        }
        return $results;
    }

    public function getNotificationsCountByUser($user) {
        $sql = "SELECT COUNT(*) FROM " . $this->getTableName() . "  WHERE user = :user";

        $bindings = array("user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

    public function markAsSeen($notifications) {
        $sql = "UPDATE " . $this->getTableName() . " SET seen = CURRENT_TIMESTAMP WHERE id in (" . implode(',', $notifications) . ")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute();

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getUnreadNotificationsCountByUser($user) {
        $sql = "SELECT COUNT(*) FROM " . $this->getTableName() . "  WHERE user = :user AND seen IS NULL";

        $bindings = array("user" => $user);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

}
