<?php

namespace Members\Tool;

use Members\Auth\Adapter;
use Members\Model\Configuration;

class Identifier
{
    /**
     * @var \Zend_Auth
     */
    var $auth = NULL;

    /**
     * @var \Zend_Auth_Result
     */
    var $authResult = NULL;

    /**
     * Identifier constructor.
     */
    public function __construct()
    {
        $this->auth = \Members\Auth\Instance::getAuth();
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function setIdentity($username = '', $password = '')
    {
        $adapterSettings = [

            'identityClassname' => Configuration::get('auth.adapter.identityClassname'),
            'identityColumn'    => Configuration::get('auth.adapter.identityColumn'),
            'credentialColumn'  => Configuration::get('auth.adapter.credentialColumn'),
            'objectPath'        => Configuration::get('auth.adapter.objectPath')

        ];

        $adapter = new Adapter($adapterSettings);
        $adapter
            ->setIdentity($username)
            ->setCredential($password);

        $this->authResult = $this->auth->authenticate($adapter);

        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->authResult->isValid();
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->authResult->getCode();
    }

    /**
     * @return bool
     */
    public function hasIdentity()
    {
        return !is_null($this->auth->getIdentity());
    }

    /**
     * @return mixed|null
     */
    public function getIdentity()
    {
        return $this->auth->getIdentity();
    }
}