<?php

namespace MembersBundle\Restriction;

use Pimcore\Model\AbstractModel;

/**
 * @method \MembersBundle\Restriction\Dao getDao()
 */
class Restriction extends AbstractModel
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $ctype = null;

    /**
     * @var string
     */
    public $targetId = 0;

    /**
     * @var bool
     */
    public $isInherited = false;

    /**
     * @var bool
     */
    public $inherit = false;

    /**
     * @var array
     */
    public $relatedGroups = [];

    /**
     * @param int $id
     *
     * @return Restriction
     */
    public static function getById($id)
    {
        $obj = new self();
        $obj->getDao()->getById($id);

        return $obj;
    }

    /**
     * @param int    $id
     * @param string $cType
     *
     * @return Restriction
     */
    public static function getByTargetId($id, $cType = 'page')
    {
        $obj = new self();
        $obj->getDao()->getByField('targetId', (int) $id, $cType);

        return $obj;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function setId($id)
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
     *
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
     * @param int $targetId
     *
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
     *
     * @return static
     */
    public function setRelatedGroups($relatedGroups)
    {
        $relatedGroups = (array) $relatedGroups;
        $this->relatedGroups = array_map('intval', $relatedGroups);

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsInherited()
    {
        return $this->isInherited;
    }

    /**
     * Alias for getIsInherited().
     *
     * @return bool
     */
    public function isInherited()
    {
        return $this->getIsInherited();
    }

    /**
     * @param bool $isInherited
     *
     * @return static
     */
    public function setIsInherited($isInherited)
    {
        $this->isInherited = (bool) $isInherited;

        return $this;
    }

    /**
     * @return bool
     */
    public function getInherit()
    {
        return $this->inherit;
    }

    /**
     * @param bool $inherit
     *
     * @return static
     */
    public function setInherit($inherit)
    {
        $this->inherit = (bool) $inherit;

        return $this;
    }
}
