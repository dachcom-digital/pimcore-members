<?php

namespace Members\View\Helper;

class MembersAuthHelper extends \Zend_View_Helper_Abstract {

    /**
     * @var \Zend_Auth
     */
    private static $_auth;

    public function __construct()
    {
        self::$_auth = \Zend_Auth::getInstance();
    }

    public function membersauthhelper()
    {
        return $this;
    }

    public function isLoggedIn()
    {
        if( self::$_auth->getIdentity())
        {
            return TRUE;
        }

        return FALSE;
    }

    public function getSetting($value)
    {
        return \Members\Model\Configuration::getLocalizedPath($value);
    }

    public function getUser()
    {
        return self::$_auth->getIdentity();
    }
}