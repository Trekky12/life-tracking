<?php

namespace App\Board\Card;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards_cards";
    protected $model = "\App\Board\Card\Card";
    protected $select_results_of_user_only = false;
    protected $insert_user = false;
    protected $has_user_table = true;
    protected $user_table = "boards_cards_user";
    protected $element_name = "card";

    public function getCardsFromStack($stack, $archive = 0) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE stack = :stack ";

        $bindings = ["stack" => $stack];

        if ($archive == 0) {
            $sql .= " AND archive = :archive ";
            $bindings["archive"] = $archive;
        }

        $sql .= "ORDER BY position, createdOn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function updatePosition($id, $position, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET position=:position, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "position" => $position,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function moveCard($id, $stack, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET stack=:stack, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "stack" => $stack,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function setArchive($id, $archive, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET archive=:archive, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "archive" => $archive,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s'),
            "changedBy" => $user
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return true;
    }

    public function getCardsUser() {
        $sql = "SELECT card, user FROM " . $this->getTableName($this->user_table) . "";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = intval($row["card"]);
            if (!array_key_exists($key, $results)) {
                $results[$key] = array();
            }
            $results[$key][] = intval($row["user"]);
        }
        return $results;
    }

    public function getCardBoard($id) {
        $sql = "SELECT st.board FROM " . $this->getTableName() . " ca, " . $this->getTableName("boards_stacks") . " st WHERE ca.id = :id AND ca.stack = st.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "id" => $id
        ]);

        if ($stmt->rowCount() > 0) {
            return intval($stmt->fetchColumn());
        }
        throw new \Exception($this->translation->getTranslatedString('NO_DATA'));
    }

    public function getCardReminder() {
        $sql = "SELECT cu.user as user, c.id, c.date, c.time, c.title, c.date = CURDATE() as today, b.name as board, b.hash, s.name as stack "
                . "FROM " . $this->getTableName() . " c, "
                . "     " . $this->getTableName("boards_stacks") . " s,  "
                . "     " . $this->getTableName("boards") . " b, "
                . "     " . $this->getTableName($this->user_table) . " cu "
                . " WHERE c.stack = s.id AND s.board = b.id "
                . " AND cu.card = c.id "
                . " AND c.archive = :archive "
                . " AND date <= CURDATE() ";

        $bindings = ["archive" => 0];


        $sql .= "ORDER BY date DESC, time DESC, board";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        $results = [];
        while ($row = $stmt->fetch()) {
            $user = intval($row["user"]);
            $today = intval($row["today"]);
            $board = $row["board"];
            $stack = $row["stack"];

            /**
             * First array dimension is the user
             */
            if (!array_key_exists($user, $results)) {
                $results[$user] = array();
            }

            /**
             * Second array dimension is the date (today/not today)
             */
            if (!array_key_exists($today, $results[$user])) {
                $results[$user][$today] = array();
            }

            /**
             * Third array dimension is the board with stacks
             */
            if (!array_key_exists($board, $results[$user][$today])) {
                $results[$user][$today][$board]["hash"] = $row["hash"];
                $results[$user][$today][$board]["stacks"] = array();
            }

            /**
             * Fourth array dimension is the stack with cards
             */
            if (!array_key_exists($stack, $results[$user][$today][$board]["stacks"])) {
                $results[$user][$today][$board]["stacks"][$stack] = array();
            }


            $results[$user][$today][$board]["stacks"][$stack][] = $row;
        }
        return $results;
    }

}
