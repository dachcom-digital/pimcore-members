<?php

namespace Members\Model\Restriction;

use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
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
    public function getById($id) {

        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE id  = ?', $id);

        if( $data === FALSE)
        {
            throw new \Exception('Object with the ID ' . $id . ' doesn\'t exists');
        }
        else
        {
           $data = $this->addRelationData($data);
        }

        $this->assignVariablesToModel($data);

    }

    /**
     * @param string    $field
     * @param null      $value
     * @param string    $cType
     *
     * @throws \Exception
     */
    public function getByField($field, $value = null, $cType = 'page') {

        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE ' . $field . ' = ? AND ctype = ?', array($value, $cType));

        if( $data === FALSE)
        {
            throw new \Exception('Object (Type: ' . $cType. ') with the ' . $field . ' ' . $value . ' doesn\'t exists');
        }
        else
        {
            $data = $this->addRelationData($data);

        }

        $this->assignVariablesToModel($data);
    }

    /**
     * Save object to database
     *
     * @return bool
     */
    public function save()
    {
        $saveData = array(

            'targetId'      => $this->model->getTargetId(),
            'ctype'         => $this->model->getCtype(),
            'isInherited'   => (int) $this->model->isInherited(),
            'inherit'   => (int) $this->model->getInherit()

        );

        if($this->model->getId() !== null)
        {
            $this->db->update($this->tableName, $saveData, $this->db->quoteInto("id = ?", $this->model->getId()));
        }
        else
        {
            $this->db->insert($this->tableName, $saveData);
            $this->model->setId($this->db->lastInsertId());
        }

        $this->saveRelations();

        return TRUE;

    }

    private function addRelationData( $data )
    {
        $relations = $this->db->fetchAll('SELECT * FROM ' . $this->tableRelationName . ' WHERE restrictionId  = ?', $data['id'] );

        if( $relations !== FALSE)
        {
            foreach($relations as $relation)
            {
                $data['relatedGroups'][] = $relation['groupId'];
            }
        }

        return $data;

    }

    /**
     * @return bool
     * @throws \Zend_Db_Adapter_Exception
     */
    public function saveRelations()
    {
        $groups = $this->model->getRelatedGroups();

        //first, delete all!
        $this->db->delete($this->tableRelationName, $this->db->quoteInto("restrictionId = ?", $this->model->getId()));

        //set related Groups
        if( empty( $groups ) )
        {
            return FALSE;
        }

        foreach( $groups as $groupId)
        {
            $saveData = array(

                'restrictionId' => $this->model->getId(),
                'groupId' => (int) $groupId,

            );

            $this->db->insert($this->tableRelationName, $saveData);

        }

        return TRUE;

    }

    /**
     * Delete Data
     */
    public function delete() {

        $this->db->delete($this->tableName, $this->db->quoteInto("id = ?", $this->model->getId()));
        $this->db->delete($this->tableRelationName, $this->db->quoteInto("restrictionId = ?", $this->model->getId()));
    }

}
