<?php

namespace Members\Model;

use Pimcore\Model\AbstractModel;

class Restriction extends AbstractModel
{

    /**
     * @var string
     */
    public $targetId;

    /**
     * @var integer
     */
    public $id;

    /**
     * @var boolean
     */
    public $inheritable;

    /**
     * @var boolean
     */
    public $inherited = false;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return boolean
     */
    public function getInherited()
    {
        return $this->inherited;
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
     * @param boolean $inherited
     * @return static
     */
    public function setInherited($inherited)
    {
        $this->inherited = (bool) $inherited;
        return $this;
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
