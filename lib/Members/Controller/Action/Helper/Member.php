<?php

namespace Members\Controller\Action\Helper;

use Members\Model\Configuration;
use Pimcore\Model\Object;

class Member extends \Zend_Controller_Action_Helper_Abstract
{
    /**
     * @param bool $fromStorage
     * @return Object\Member|null
     */
    public function direct($fromStorage = true)
    {
        $identity = \Zend_Auth::getInstance()->getIdentity();
        if ($identity && !$fromStorage)
        {
            // return real object instead of object cached in auth storage
            $identity = Object\Member::getById($identity->getId());
        }

        return $identity;

    }

    /**
     * Check if user is logged in.
     * Redirect to login page with return URL.
     */
    public function requireAuth()
    {
        if (!\Zend_Auth::getInstance()->hasIdentity())
        {
            $this->getActionController()->redirect(sprintf('%s?back=%s',
                Configuration::getLocalizedPath('routes.login'),
                urlencode( $this->getRequest()->getRequestUri() )
            ));
        }
    }

}

// unfortunately we need this alias here, since ZF plugin loader isn't able to handle namespaces correctly
class_alias("Members\\Controller\\Action\\Helper\\Member", "Members_Controller_Action_Helper_Member");