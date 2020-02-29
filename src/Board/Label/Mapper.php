<?php

namespace App\Board\Label;

class Mapper extends \App\Base\Mapper {

    protected $table = "boards_labels";
    protected $model = "\App\Board\Label\Label";
    protected $select_results_of_user_only = false;
    protected $insert_user = true;

    public function getLabelsFromBoard($board) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE board = :board ORDER BY changedOn";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "board" => $board
        ]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = reset($row);
            $results[$key] = new $this->model($row);
        }
        return $results;
    }

    public function deleteLabelsFromCard($card) {
        $sql = "DELETE FROM " . $this->getTableName("boards_cards_label") . "  WHERE card = :card";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "card" => $card,
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('DELETE_FAILED'));
        }
        return true;
    }

    public function addLabelsToCard($card, $labels = array()) {
        $data_array = array();
        $keys_array = array();
        foreach ($labels as $idx => $label) {
            $data_array["card" . $idx] = $card;
            $data_array["label" . $idx] = $label;
            $keys_array[] = "(:card" . $idx . " , :label" . $idx . ")";
        }

        $sql = "INSERT INTO " . $this->getTableName("boards_cards_label") . " (card, label) "
                . "VALUES " . implode(", ", $keys_array) . "";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data_array);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('SAVE_NOT_POSSIBLE'));
        } else {
            return $this->db->lastInsertId();
        }
    }

    public function getLabelsFromCard($card) {
        $sql = "SELECT label FROM " . $this->getTableName("boards_cards_label") . " WHERE card = :card";

        $bindings = array("card" => $card);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        $results = [];
        while ($el = $stmt->fetchColumn()) {
            $results[] = intval($el);
        }
        return $results;
    }

    public function getCardsLabel() {
        $sql = "SELECT card, label FROM " . $this->getTableName("boards_cards_label") . "";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch()) {
            $key = intval($row["card"]);
            if (!array_key_exists($key, $results)) {
                $results[$key] = array();
            }
            $results[$key][] = intval($row["label"]);
        }
        return $results;
    }

}
