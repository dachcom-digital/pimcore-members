<?php

namespace Members\Tool;

use Members\Auth\Adapter;
use Members\Model\Configuration;

class Identifier {

    /**
     * @var \Zend_Auth
     */
    var $auth = NULL;

    /**
     * @var \Zend_Auth_Result
     */
    var $authResult = null;

    public function __construct()
    {
        $this->auth = \Zend_Auth::getInstance();
    }

    public function setIdentity( $username = '', $password = '' )
    {
        $adapterSettings = array(

            'identityClassname'     =>  Configuration::get('auth.adapter.identityClassname'),
            'identityColumn'        =>  Configuration::get('auth.adapter.identityColumn'),
            'credentialColumn'      =>  Configuration::get('auth.adapter.credentialColumn'),
            'objectPath'            =>  Configuration::get('auth.adapter.objectPath')

        );

        $adapter = new Adapter( $adapterSettings );
        $adapter
            ->setIdentity($username)
            ->setCredential($password);

        $this->authResult = $this->auth->authenticate($adapter);

        return $this;

    }

    public function isValid()
    {
        return $this->authResult->isValid();
    }

    public function getCode()
    {
        return $this->authResult->getCode();
    }
}