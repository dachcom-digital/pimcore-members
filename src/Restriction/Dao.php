<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Restriction;

use Doctrine\DBAL\Exception;
use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{
    protected string $tableName = 'members_restrictions';
    protected string $tableRelationName = 'members_group_relations';

    /**
     * @var Restriction
     */
    protected $model;

    /**
     * @param int $id
     *
     * @throws \Exception
     */
    public function getById($id)
    {
        $data = $this->db->fetchAssociative('SELECT * FROM ' . $this->tableName . ' WHERE id  = ?', [$id]);

        if ($data === false) {
            throw new \Exception('Object with the ID ' . $id . ' doesn\'t exists');
        }

        $data = $this->addRelationData($data);
        $this->assignVariablesToModel($data);
    }

    /**
     * @param string $field
     * @param null   $value
     * @param string $cType
     *
     * @throws \Exception
     */
    public function getByField($field, $value = null, $cType = 'page')
    {
        $data = $this->db->fetchAssociative('SELECT * FROM ' . $this->tableName . ' WHERE ' . $field . ' = ? AND ctype = ?', [
            $value,
            $cType
        ]);

        if ($data === false) {
            throw new \Exception('Object (Type: ' . $cType . ') with the ' . $field . ' ' . $value . ' doesn\'t exists');
        }

        $data = $this->addRelationData($data);
        $this->assignVariablesToModel($data);
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function save()
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
        $relations = $this->db->fetchAllAssociative('SELECT * FROM ' . $this->tableRelationName . ' WHERE restrictionId  = ?', [$data['id']]);

        foreach ($relations as $relation) {
            $data['relatedGroups'][] = $relation['groupId'];
        }

        return $data;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function saveRelations()
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

    /**
     * @throws Exception
     */
    public function delete()
    {
        $this->db->delete($this->tableName, ['id' => $this->model->getId()]);
        $this->db->delete($this->tableRelationName, ['restrictionId' => $this->model->getId()]);
    }
}
