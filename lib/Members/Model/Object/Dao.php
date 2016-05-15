<?php

namespace Members\Model\Object;

use Members\Tool;

class Dao extends \Pimcore\Model\Object\Concrete\Dao
{
    /**
     * Because Pimcore stores objects in cache, this method is deprecated.
     * @deprecated
     */
    public function getData()
    {
        /**
            parent::getData();
            $restriction = Tool\Observer::isRestrictedObject( $this->model );
            $this->model->setValue('restricted', $restriction['section'] === Tool\Observer::SECTION_NOT_ALLOWED);
        **/
    }
}