<?php

namespace Members\Model;

use Pimcore\Model\AbstractModel;
use Pimcore\Model\Object\AbstractObject;

class Restriction extends AbstractModel
{

    /**
     * @var integer
     */
    public $id = NULL;

    /**
     * @var string
     */
    public $ctype = NULL;

    /**
     * @var string
     */
    public $targetId = 0;

    /**
     * @var boolean
     */
    public $inheritable = FALSE;

    /**
     * @var boolean
     */
    public $inherited = FALSE;

    /**
     * @var array
     */
    public $relatedGroups = array();

    /**
     * @param $id
     *
     * @return \Members\Model\Restriction
     */
    public static function getById($id) {

        $obj = new self;
        $obj->getDao()->getById($id);
        return $obj;

    }

    /**
     * @param $id
     * @param $cType
     *
     * @return \Members\Model\Restriction
     */
    public static function getByTargetId($id, $cType = 'page') {

        $obj = new self;
        $obj->getDao()->getByField('targetId', (int) $id, $cType);
        return $obj;
    }

    /**
     * @param null   $docId
     * @param int    $docParentIds
     * @param string $cType
     *
     * @return \Members\Model\Restriction
     */
    public static function findNextInherited( $docId = NULL, $docParentIds, $cType = 'page' )
    {
        $obj = new self;
        $obj->getDao()->getNextInheritedParent($docId, $docParentIds, $cType);
        return $obj;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function setId( $id )
    {
        return $this->id = (int) $id;
    }

    /**
     * @return string
     */
    public function getCtype()
    {
        return $this->ctype;
    }

    /**
     * @param string $cType
     * @return static
     */
    public function setCtype($cType)
    {
        $this->ctype = (string) $cType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param integer $targetId
     * @return static
     */
    public function setTargetId($targetId)
    {
        $this->targetId = (int) $targetId;
        return $this;
    }

    /**
     * @return array
     */
    public function getRelatedGroups()
    {
        return $this->relatedGroups;
    }

    /**
     * @param array $relatedGroups
     * @return static
     */
    public function setRelatedGroups($relatedGroups)
    {
        $relatedGroups = (array) $relatedGroups;
        $this->relatedGroups = array_map('intval', $relatedGroups);

        return $this;
    }

    /**
     * @return boolean
     */
    public function getInherited()
    {
        return $this->inherited;
    }

    /**
     * @param boolean $inherited
     * @return static
     */
    public function setInherited($inherited)
    {
        $this->inherited = (bool) $inherited;
        return $this;
    }

    /**
     * Alias for getInherited()
     *
     * @return boolean
     */
    public function isInherited()
    {
        return $this->getInherited();
    }

    /**
     * @return boolean
     */
    public function getInheritable()
    {
        return $this->inheritable;
    }

    /**
     * @param boolean $inheritable
     * @return static
     */
    public function setInheritable($inheritable)
    {
        $this->inheritable = (bool) $inheritable;
        return $this;
    }

}
