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

}
