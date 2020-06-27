<?php

namespace App\Domain\User\ApplicationPasswords;

class ApplicationPasswordMapper extends \App\Domain\Mapper {

    protected $table = 'global_users_application_passwords';
    protected $dataobject = \App\Domain\User\ApplicationPasswords\ApplicationPassword::class;
    protected $select_results_of_user_only = true;
    protected $insert_user = true;

    public function getPasswords($login) {
        $sql = "SELECT ap.name as name, ap.password as pw FROM " . $this->getTableName() . " ap, " . $this->getTableName("global_users") . " u "
                . " WHERE u.login = :login "
                . " AND u.id = ap.user";

        $bindings = array("login" => $login);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = ["name" => $row["name"], "pw" => $row["pw"]];
        }
        return $results;
    }

}
