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
     * @param      $field
     * @param null $value
     *
     * @throws \Exception
     */
    public function getByField($field, $value = null) {

        $data = $this->db->fetchRow('SELECT * FROM ' . $this->tableName . ' WHERE ' . $field . ' = ?', $value);

        if( $data === FALSE)
        {
            throw new \Exception('Object with the '.$field.' ' . $value . ' doesn\'t exists');
        }
        else
        {
            $data = $this->addRelationData($data);

        }

        $this->assignVariablesToModel($data);
    }

    public function getNextInheritedParent( $docId = NULL, $docParentIds ) {

        $propertiesRaw = $this->db->fetchAll("SELECT * FROM members_restrictions WHERE ((targetId IN (" . implode(",", $docParentIds) . ") AND inheritable = 1) OR targetId = ? )", $docId);

        // because this should be faster than mysql
        usort($propertiesRaw, function ($left, $right) {
            return strcmp($left["targetId"], $right["targetId"]);
        });

        if( !empty( $propertiesRaw ) )
        {
            $data = $this->addRelationData($propertiesRaw[0]);
            $this->assignVariablesToModel($data);
        }
    }

    /**
     * Save object to database
     *
     * @return bool
     */
    public function save()
    {
        $saveData = array(

            'targetId' => $this->model->getTargetId(),
            'inheritable' => (int)$this->model->getInheritable()

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
     *
     */
    public function delete() {

        $this->db->delete($this->tableName, $this->db->quoteInto("id = ?", $this->model->getId()));
        $this->db->delete($this->tableRelationName, $this->db->quoteInto("restrictionId = ?", $this->model->getId()));
    }

}
