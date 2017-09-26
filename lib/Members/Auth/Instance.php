<?php

namespace Members\Auth;

use Members\Auth\Storage;

class Instance {

    /**
     * @return \Zend_Auth
     */
    public static function getAuth()
    {
        $a = \Zend_Auth::getInstance();

        if(defined('MEMBERS_API_MODE') && MEMBERS_API_MODE === TRUE) {
            $a->setStorage(new Storage\Flow());
        } else {
            $a->setStorage(new Storage\Pimcore());
        }

        return $a;
    }
}