<?php

namespace Members\Auth;

use Members\Auth\Storage;

class Instance
{
    /**
     * @var \Zend_Auth
     */
    protected static $auth;

    /**
     * @return \Zend_Auth
     */
    public static function getAuth()
    {
        if (self::$auth instanceof \Zend_Auth) {
            return self::$auth;
        }

        self::$auth = \Zend_Auth::getInstance();

        if (defined('MEMBERS_API_MODE') && MEMBERS_API_MODE === TRUE) {
            self::$auth->setStorage(new Storage\Flow());
        } else {
            self::$auth->setStorage(new Storage\Pimcore());
        }

        return self::$auth;
    }
}