<?php

namespace App\Domain\Timesheets\Reminder;

class ReminderMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_reminders";
    protected $dataobject = \App\Domain\Timesheets\Reminder\Reminder::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getFromProject($id, $type = null) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE project = :id ";

        $bindings = array("id" => $id);

        if (!is_null($type)) {
            $sql .= " AND type = :type";
            $bindings["type"] = $type;
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

    public function wasNotificationSent(int $project, int $reminder, ?int $timesheet = null): bool {
        $sql = "SELECT COUNT(*) as count FROM " . $this->getTableName("timesheets_reminders_sent") . "  WHERE project = :project AND reminder = :reminder";

        $bindings = [
            'project' => $project,
            'reminder' => $reminder
        ];

        if ($timesheet !== null) {
            $sql .= " AND timesheet = :timesheet";
            $bindings['timesheet'] = $timesheet;
        } else {
            $sql .= " AND timesheet IS NULL AND DATE(createdOn) = CURDATE()";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    public function markAsSent(int $project, int $reminder, ?int $timesheet = null): int {
        $sql = "INSERT INTO " . $this->getTableName("timesheets_reminders_sent") . " (project, reminder, timesheet)
            VALUES (:project, :reminder, :timesheet)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'project' => $project,
            'reminder' => $reminder,
            'timesheet' => $timesheet
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getRemindersByProject(){
        $sql = "SELECT r.*, nc.id as category FROM " . $this->getTableName() . " r, " . $this->getTableName("notifications_categories") . " nc WHERE r.id = nc.reminder ORDER BY project";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = $row["project"];
            $results[$key][] = new $this->dataobject($row);
        }
        return $results;
    }

    public function getMessages($reminder_id) {
        $sql = "SELECT id, message, send_count FROM " . $this->getTableName("timesheets_reminders_messages") . " WHERE reminder = :reminder ORDER BY id";

        $bindings = [
            "reminder" => $reminder_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }

    
    public function addMessages($reminder_id, $messages = [], $send_count = null) {

        $data_array = [];
        $keys_array = [];
        foreach ($messages as $idx => $message) {
            $data_array["reminder" . $idx] = $reminder_id;
            $data_array["message" . $idx] = $message["message"];
            $data_array["send_count" . $idx] = $send_count;
            $keys_array[] = "(:reminder" . $idx . " , :message" . $idx . ", :send_count" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("timesheets_reminders_messages") . " (reminder, message, send_count) "
            . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function updateMessages($reminder_id, $messages = []) {

        $sql = "UPDATE " . $this->getTableName("timesheets_reminders_messages") . "
            SET message = :message
            WHERE id = :id AND reminder = :reminder";

        $stmt = $this->db->prepare($sql);

        foreach ($messages as $message) {
            $result = $stmt->execute([
                ':id'       => $message['id'],
                ':reminder'     => $reminder_id,
                ':message' => $message['message']
            ]);

            if (!$result) {
                throw new \Exception(
                    $this->translation->getTranslatedString('UPDATE_FAILED')
                );
            }
        }

        return true;
    }

    public function deleteMessages($reminder_id, $messages = []) {
        $sql = "DELETE FROM " . $this->getTableName("timesheets_reminders_messages") . "  WHERE reminder = :reminder";

        $bindings = [
            "reminder" => $reminder_id
        ];

        if (!empty($messages)) {
            $keys_array = [];
            foreach ($messages as $idx => $message_id) {
                $bindings["message" . $idx] = $message_id;
                $keys_array[] = ":message" . $idx;
            }
            $sql .= " AND id IN (" . implode(',', $keys_array) . ")";
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function getNextMessage($reminder_id) {
        $sql = "SELECT id, message
                FROM " . $this->getTableName("timesheets_reminders_messages") . " 
                WHERE reminder = :reminder 
                AND send_count = (
                    SELECT MIN(send_count)
                    FROM " . $this->getTableName("timesheets_reminders_messages") . "
                    WHERE reminder = :reminder
                )
                ORDER BY RAND()
                LIMIT 1";

        $bindings = [
            "reminder" => $reminder_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function addMessageSent($id) {
        $sql = "UPDATE " . $this->getTableName("timesheets_reminders_messages") . " SET send_count = send_count  + :value WHERE id  = :id";
        $bindings = [
            "id" => $id,
            "value" => 1
        ];
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getMinSendCount($reminder_id) {
        $sql = "SELECT MIN(send_count)
                FROM " . $this->getTableName("timesheets_reminders_messages") . "
                WHERE reminder = :reminder
                LIMIT 1";

        $bindings = [
            "reminder" => $reminder_id
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return 0;
    }

}
