<?php

namespace App\Domain\Home\Widget;

class WidgetMapper extends \App\Domain\Mapper {

    protected $table = 'global_widgets';
    protected $dataobject = \App\Domain\Home\Widget\WidgetObject::class;

    public function updatePosition($id, $position) {
        $sql = "UPDATE " . $this->getTableName() . " SET position=:position, changedOn =:changedOn WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "position" => $position,
            "id" => $id,
            "changedOn" => date('Y-m-d H:i:s')
        ]);
        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
    }

    public function getWidget($id) {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE id = :id";

        $bindings = array("id" => $id);
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);

        if ($stmt->rowCount() > 0) {
            return new $this->dataobject($stmt->fetch());
        }
        return null;
    }

    /**
     * Override default and update only options
     */
    public function update(\App\Domain\DataObject $data, $parameter = null) {
        $sql = "UPDATE " . $this->getTableName() . " SET options = :options, changedOn = :changedOn WHERE id = :id";

        $bindings = [
            "id" => $data->id,
            "options" => json_encode($data->options),
            "changedOn" => date('Y-m-d H:i:s'),
        ];
        $this->addSelectFilterForUser($sql, $bindings);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($bindings);

        if (!$result) {
            throw new \Exception($this->translation->getTranslatedString('UPDATE_FAILED'));
        }
        return $stmt->rowCount();
    }

}
