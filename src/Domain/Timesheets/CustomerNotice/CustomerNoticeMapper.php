<?php

namespace App\Domain\Timesheets\CustomerNotice;

use App\Domain\DataObject;

class CustomerNoticeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_notices";
    protected $dataobject = \App\Domain\Timesheets\CustomerNotice\CustomerNotice::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function __construct(\PDO $db, \App\Domain\Main\Translator $translation, \App\Domain\Base\CurrentUser $user) {
        parent::__construct($db, $translation, $user);
    }

    public function getNotice($customer_id) {
        $sql = "SELECT *, notice FROM " . $this->getTableName() . "  WHERE customer = :customer AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'customer' => $customer_id
        ]);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function hasNotice($customer_id) {
        $sql = "SELECT id FROM " . $this->getTableName() . "  WHERE customer = :customer AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'customer' => $customer_id
        ]);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

    public function hasNotices($customer_ids = []) {
        if (empty($customer_ids)) {
            return [];
        }
        $sql = "SELECT customer FROM " . $this->getTableName();

        $notice_bindings = [];
        foreach ($customer_ids as $idx => $customer_id) {
            $notice_bindings[":customer" . $idx] = $customer_id;
        }

        $sql .= " WHERE customer IN (" . implode(',', array_keys($notice_bindings)) . ") AND is_active = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($notice_bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row["customer"];
        }
        return $results;
    }

    public function insert(DataObject $data) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE " . $this->getTableName() . " SET is_active = 0 WHERE customer = :customer ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(["customer" => $data->customer]);

            $inserted_id = parent::insert($data);

            $this->db->commit();

            return $inserted_id;
        } catch (\PDOException $e) {
            $this->db->rollBack();
        }

        return null;
    }
}
