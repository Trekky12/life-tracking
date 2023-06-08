<?php

namespace App\Domain\Notifications;

class MailNotificationCategoryMapper extends \App\Domain\Mapper {

    protected $table = 'mail_categories';
    protected $select_results_of_user_only = false;
    protected $insert_user = false;

    public function getCategoryByIdentifier($name) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE identifier = :name";

        $bindings = array("name" => strtolower($name));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        throw new \Exception($this->translation->getTranslatedString('ELEMENT_NOT_FOUND'), 404);
    }

}
