<?php

namespace App\Domain\Timesheets\SheetNotice;

use \App\Domain\Base\Settings;

class SheetNoticeMapper extends \App\Domain\Mapper {

    protected $table = "timesheets_sheets_notices";
    protected $dataobject = \App\Domain\Timesheets\SheetNotice\SheetNotice::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;
    private $KEY = null;

    public function __construct(\PDO $db, \App\Domain\Main\Translator $translation, \App\Domain\Base\CurrentUser $user, Settings $settings) {
        parent::__construct($db, $translation, $user);

        $this->KEY = hash('sha256', $settings->getAppSettings()['timesheets']['key']);
        
    }

    public function setNotice($id, $notice) {
        $sql = "UPDATE " . $this->getTableName() . " SET notice2  = AES_ENCRYPT(:notice, '" . $this->KEY . "') WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'notice' => $notice,
            'id' => $id
        ]);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getNotice($sheet_id) {
        $sql = "SELECT *, "
                ." CASE "
                . " WHEN encrypted < 1 THEN CAST( AES_DECRYPT(notice2, '" . $this->KEY . "') AS CHAR) "
                . " ELSE notice "
                . "END as notice "
                . "FROM " . $this->getTableName() . "  "
                . "WHERE sheet = :sheet";

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
