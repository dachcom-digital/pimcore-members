<?php

namespace Members\Auth;

class Instance
{
    /**
     * @return \Zend_Auth
     */
    public static function getAuth()
    {
        $a = \Zend_Auth::getInstance();
        $a->setStorage(new \Members\Auth\Storage\Pimcore());

        return $a;
    }
}