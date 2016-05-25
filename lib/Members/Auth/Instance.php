<?php

namespace Members\Auth;

class Instance {

    public static function getAuth()
    {
        $a = \Zend_Auth::getInstance();
        $a->setStorage(new \Members\Auth\Storage\Pimcore());
        return $a;
    }
}