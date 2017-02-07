<?php

namespace Members\View\Helper;

use Members\Tool\Identifier;

class MembersAuthHelper extends \Zend_View_Helper_Abstract
{
    /**
     * @var \Zend_Auth
     */
    private static $_auth;

    /**
     * MembersAuthHelper constructor.
     */
    public function __construct()
    {
        $identifier = new Identifier();
        self::$_auth = $identifier->getIdentity();
    }

    /**
     * @return $this
     */
    public function membersAuthHelper()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        if (!is_null(self::$_auth)) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param      $value
     * @param bool $localizeValue
     *
     * @return mixed|null
     */
    public function getSetting($value, $localizeValue = FALSE)
    {
        if ($localizeValue === TRUE) {
            return \Members\Model\Configuration::getLocalizedPath($value);
        }

        return \Members\Model\Configuration::get($value);
    }

    /**
     * @return mixed|null|\Zend_Auth
     */
    public function getUser()
    {
        return self::$_auth;
    }
}