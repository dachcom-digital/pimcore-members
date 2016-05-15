<?php

namespace Members\Model;

use Pimcore\Model\Object\Concrete;

class Object extends Concrete {

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
        return $this->restricted;
    }
}