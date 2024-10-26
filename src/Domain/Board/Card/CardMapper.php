<?php

namespace App\Domain\Board\Card;

class CardMapper extends \App\Domain\Mapper {

    protected $table = "boards_cards";
    protected $dataobject = \App\Domain\Board\Card\Card::class;
    protected $select_results_of_user_only = false;
    protected $insert_user = false;
    protected $has_user_table = true;
    protected $user_table = "boards_cards_user";
    protected $element_name = "card";

    public function getUserCards($id) {
        $sql = "SELECT ca.id FROM " . $this->getTableName("boards_user") . " ub, " . $this->getTableName("boards_stacks") . " st, " . $this->getTableName("boards_cards") . " ca "
            . " WHERE ub.user = :id "
            . " AND st.board = ub.board "
            . " AND st.id = ca.stack";

        $bindings = array("id" => $id);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    public function getCardsFromStack($stack, $card_users = [], $card_labels = [], $archive = 0) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE stack = :stack ";

        $bindings = ["stack" => $stack];

        if ($archive == 0) {
            $sql .= " AND archive = :archive ";
            $bindings["archive"] = $archive;
        }

        $sql .= "ORDER BY position, changedOn, createdOn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $card_id = intval($row["id"]);
            $card = new $this->dataobject($row);

            $card->users = array_key_exists($card_id, $card_users) ? $card_users[$card_id] : [];
            $card->labels = array_key_exists($card_id, $card_labels) ? $card_labels[$card_id] : [];

            $results[] = $card;
        }
        return $results;
    }

    public function getCardIDsFromStack($stack, $archive = null) {
        $sql = "SELECT id, title, archive FROM " . $this->getTableName() . " WHERE stack = :stack ";

        $bindings = ["stack" => $stack];

        if (!is_null($archive)) {
            $sql .= " AND archive = :archive ";
            $bindings["archive"] = $archive;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = $row;
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

    public function moveCard($id, $stack, $position, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET stack=:stack, position = :position, changedOn =:changedOn, changedBy =:changedBy WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "stack" => $stack,
            "position" => $position,
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

    public function setArchiveByStack($stack_id, $archive, $user) {
        $sql = "UPDATE " . $this->getTableName() . " SET archive=:archive, changedOn =:changedOn, changedBy =:changedBy WHERE stack=:stack_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "archive" => $archive,
            "stack_id" => $stack_id,
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
        $sql = "SELECT cu.user as user, c.id, c.date, c.time, c.title, c.date = CURDATE() as today, b.name as board_name, b.hash, b.id as board_id, s.name as stack "
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
            $board = $row["board_id"];
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
                $results[$user][$today][$board]["name"] = $row["board_name"];
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

    public function getCardsWidget($stack, $due = false, $archive = 0) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE stack = :stack ";

        $bindings = ["stack" => $stack];

        $sql .= " AND archive = :archive ";
        $bindings["archive"] = $archive;

        if ($due) {
            $sql .= " AND date <= CURDATE() ";
        }

        $sql .= "ORDER BY position, changedOn, createdOn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);


        $results = [];
        while ($row = $stmt->fetch()) {
            $card_id = intval($row["id"]);
            $card = new $this->dataobject($row);

            $results[] = $card;
        }
        return $results;
    }
}
