<?php

namespace MembersBundle\Restriction;

use Pimcore\Model;

class Resource extends Model\Dao\AbstractDao
{
    /**
     * @var string
     */
    protected $tableName = 'members_restrictions';

    /**
     * @var string
     */
    protected $tableRelationName = 'members_group_relations';

    /**
     * @param $id
     *
     * @throws \Exception
     */
    public function getById($id)
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE id  = ?', [$id]);

        if ($data === FALSE) {
            throw new \Exception('Object with the ID ' . $id . ' doesn\'t exists');
        } else {
            $data = $this->addRelationData($data);
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * @param string $field
     * @param null   $value
     * @param string $cType
     *
     * @throws \Exception
     */
    public function getByField($field, $value = NULL, $cType = 'page')
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE ' . $field . ' = ? AND ctype = ?', [$value, $cType]);

        if ($data === FALSE) {
            throw new \Exception('Object (Type: ' . $cType . ') with the ' . $field . ' ' . $value . ' doesn\'t exists');
        } else {
            $data = $this->addRelationData($data);
        }

        $this->assignVariablesToModel($data);
    }

    /**
     * Save object to database
     * @return bool
     */
    public function save()
    {
        $saveData = [

            'targetId'    => $this->model->getTargetId(),
            'ctype'       => $this->model->getCtype(),
            'isInherited' => (int)$this->model->isInherited(),
            'inherit'     => (int)$this->model->getInherit()

        ];

        if ($this->model->getId() !== NULL) {
            $this->db->update($this->tableName, $saveData, ['id' => $this->model->getId()]);
        } else {
            $this->db->insert($this->tableName, $saveData);
            $this->model->setId($this->db->lastInsertId());
        }

        $this->saveRelations();

        return TRUE;
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function addRelationData($data)
    {
        $relations = $this->db->fetchAll('SELECT * FROM ' . $this->tableRelationName . ' WHERE restrictionId  = ?', [$data['id']]);

        if ($relations !== FALSE) {
            foreach ($relations as $relation) {
                $data['relatedGroups'][] = $relation['groupId'];
            }
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function saveRelations()
    {
        $groups = $this->model->getRelatedGroups();

        //first, delete all!
        $this->db->delete($this->tableRelationName, ['restrictionId' => $this->model->getId()]);

        //set related Groups
        if (empty($groups)) {
            return FALSE;
        }

        foreach ($groups as $groupId) {
            $saveData = [
                'restrictionId' => $this->model->getId(),
                'groupId'       => (int)$groupId,
            ];

            $this->db->insert($this->tableRelationName, $saveData);
        }

        return TRUE;
    }

    /**
     * Delete Data
     */
    public function delete()
    {
        $this->db->delete($this->tableName, ['id' => $this->model->getId()]);
        $this->db->delete($this->tableRelationName, ['restrictionId' => $this->model->getId()]);
    }

}
