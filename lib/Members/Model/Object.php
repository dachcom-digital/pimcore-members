<?php

namespace Members\Model;

use Pimcore\Model\Object\Concrete;
use Members\Tool;

class Object extends Concrete
{
    /**
     * @var bool
     */
    var $restricted = FALSE;

    /**
     * @param $restricted
     */
    public function setRestricted($restricted)
    {
        $this->restricted = $restricted;
    }

    /**
     * @return bool
     */
    public function getRestricted()
    {
        $restriction = Tool\Observer::isRestrictedObject($this);
        $this->setValue('restricted', $restriction['section'] === Tool\Observer::SECTION_NOT_ALLOWED);

        return $this->restricted;
    }
}