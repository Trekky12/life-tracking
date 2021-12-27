<?php

namespace App\Domain\Timesheets\SheetNotice;

class SheetNoticeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_notices";
    protected $dataobject = \App\Domain\Timesheets\SheetNotice\SheetNotice::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function __construct(\PDO $db, \App\Domain\Main\Translator $translation, \App\Domain\Base\CurrentUser $user) {
        parent::__construct($db, $translation, $user);        
    }

    public function getNotice($sheet_id) {
        $sql = "SELECT *, notice FROM " . $this->getTableName() . "  WHERE sheet = :sheet";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'sheet' => $sheet_id
        ]);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    public function hasNotice($sheet_id) {
        $sql = "SELECT id FROM " . $this->getTableName() . "  WHERE sheet = :sheet";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'sheet' => $sheet_id
        ]);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        return null;
    }

}
