<?php

namespace MembersBundle\Restriction;

use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{
    protected string $tableName = 'members_restrictions';
    protected string $tableRelationName = 'members_group_relations';

    /**
     * @var Restriction
     */
    protected $model;

    public function getById(int $id): void
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE id  = ?', [$id]);

        if ($data === false) {
            throw new \Exception('Object with the ID ' . $id . ' doesn\'t exists');
        } else {
            $data = $this->addRelationData($data);
        }

        $this->assignVariablesToModel($data);
    }

    public function getByField(string $field, string $value = null, string $cType = 'page')
    {
        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE ' . $field . ' = ? AND ctype = ?', [
            $value,
            $cType
        ]);

        if ($data === false) {
            throw new \Exception('Object (Type: ' . $cType . ') with the ' . $field . ' ' . $value . ' doesn\'t exists');
        } else {
            $data = $this->addRelationData($data);
        }

        $this->assignVariablesToModel($data);
    }

    public function save(): bool
    {
        $saveData = [
            'targetId'    => $this->model->getTargetId(),
            'ctype'       => $this->model->getCtype(),
            'isInherited' => (int) $this->model->isInherited(),
            'inherit'     => (int) $this->model->getInherit()
        ];

        if ($this->model->getId() !== null) {
            $this->db->update($this->tableName, $saveData, ['id' => $this->model->getId()]);
        } else {
            $this->db->insert($this->tableName, $saveData);
            $this->model->setId($this->db->lastInsertId());
        }

        $this->saveRelations();

        return true;
    }

    private function addRelationData(array $data): array
    {
        $relations = $this->db->fetchAll('SELECT * FROM ' . $this->tableRelationName . ' WHERE restrictionId  = ?', [$data['id']]);

        if ($relations !== false) {
            foreach ($relations as $relation) {
                $data['relatedGroups'][] = $relation['groupId'];
            }
        }

        return $data;
    }

    public function saveRelations(): bool
    {
        $groups = $this->model->getRelatedGroups();

        //first, delete all!
        $this->db->delete($this->tableRelationName, ['restrictionId' => $this->model->getId()]);

        //set related Groups
        if (empty($groups)) {
            return false;
        }

        foreach ($groups as $groupId) {
            $saveData = [
                'restrictionId' => $this->model->getId(),
                'groupId'       => (int) $groupId,
            ];

            $this->db->insert($this->tableRelationName, $saveData);
        }

        return true;
    }

    public function delete(): void
    {
        $this->db->delete($this->tableName, ['id' => $this->model->getId()]);
        $this->db->delete($this->tableRelationName, ['restrictionId' => $this->model->getId()]);
    }
}
