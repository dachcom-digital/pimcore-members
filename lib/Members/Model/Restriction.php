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
    public $isInherited = FALSE;

    /**
     * @var boolean
     */
    public $inherit = FALSE;

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
    public function getIsInherited()
    {
        return $this->isInherited;
    }

    /**
     * Alias for getIsInherited()
     *
     * @return boolean
     */
    public function isInherited()
    {
        return $this->getIsInherited();
    }

    /**
     * @param boolean $isInherited
     * @return static
     */
    public function setIsInherited($isInherited)
    {
        $this->isInherited = (bool) $isInherited;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getInherit()
    {
        return $this->inherit;
    }

    /**
     * @param boolean $inherit
     * @return static
     */
    public function setInherit($inherit)
    {
        $this->inherit = (bool) $inherit;
        return $this;
    }

}
