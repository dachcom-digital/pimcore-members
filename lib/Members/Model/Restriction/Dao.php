<?php

namespace Members\Model\Restriction;

use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{

    /**
     * @return null
     */
    public function getRawData()
    {
        $id = $this->model->getId();
        $raw = null;

        if ($id) {
            $data = $this->db->fetchRow("SELECT * FROM properties WHERE id = ?", array($id));
            $raw = $data['data'];
        }

        return $raw;
    }

    /**
     * Save object to database
     *
     * @return void
     */
    public function save()
    {
        $saveData = array(
            'id' => $this->model->getId(),
            'targetId' => $this->model->getTargetId(),
            'inheritable' => (int)$this->model->getInheritable(),
        );

        $this->db->insertOrUpdate('members_restrictions', $saveData);
    }
}
