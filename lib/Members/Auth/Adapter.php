<?php

namespace Members\Auth;

use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\Listing;

class Adapter implements \Zend_Auth_Adapter_Interface
{
    /**
     * @var string
     */
    protected $identityClassname;

    /**
     * @var string
     */
    protected $identityColumn;

    /**
     * @var string
     */
    protected $credentialColumn;

    /**
     * @var string
     */
    protected $objectPath;

    /**
     * Identity value
     * @var string
     */
    protected $identity;

    /**
     * Credential value
     * @var string
     */
    protected $credential;

    /**
     * Constructor
     *
     * @param \Zend_Config|array $config Configuration settings example:
     *                                   'identityClassname' => '\Pimcore\Model\Object\Member'
     *                                   'identityColumn' => 'email'
     *                                   'credentialColumn' => 'password'
     *                                   'objectPath' => '/members'
     *
     * @throws \Zend_Auth_Adapter_Exception
     */
    public function __construct($config)
    {
        $options = ['identityClassname', 'identityColumn', 'credentialColumn', 'objectPath'];

        foreach ($options as $option) {
            if (!empty($config[$option])) {
                $setter = 'set' . ucfirst($option);
                $this->$setter($config[$option]);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getIdentityClassname()
    {
        return $this->identityClassname;
    }

    /**
     * @param mixed $identityClassname
     *
     * @return Adapter
     * @throws \Exception
     */
    public function setIdentityClassname($identityClassname)
    {
        if (!class_exists($identityClassname)) {
            throw new \Exception("Identity class '$identityClassname' not exist");
        }

        $obj = new $identityClassname();

        if (!$obj instanceof Concrete) {
            throw new \Exception('Identity class should be pimcore object');
        }

        $this->identityClassname = $identityClassname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentityColumn()
    {
        return $this->identityColumn;
    }

    /**
     * @param mixed $identityColumn
     *
     * @return Adapter
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->identityColumn = $identityColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredentialColumn()
    {
        return $this->credentialColumn;
    }

    /**
     * @param mixed $credentialColumn
     *
     * @return Adapter
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getObjectPath()
    {
        return $this->objectPath;
    }

    /**
     * @param mixed $objectPath
     *
     * @return Adapter
     */
    public function setObjectPath($objectPath)
    {
        $this->objectPath = $objectPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     *
     * @return Adapter
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * @param string $credential
     *
     * @return Adapter
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    /**
     * Performs an authentication attempt
     * @throws \Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return \Zend_Auth_Result
     */
    public function authenticate()
    {
        $optionsRequired = [
            'identityClassname',
            'identityColumn',
            'credentialColumn',
            'identity',
            'credential'
        ];

        foreach ($optionsRequired as $optionRequired) {
            if (empty($this->{$optionRequired})) {
                throw new \Zend_Auth_Adapter_Exception(
                    "Option '$optionRequired' must be set before authentication");
            }
        }

        $identities = $this->getIdentities();

        if (count($identities) == 0) {
            return new \Zend_Auth_Result(\Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, NULL);
        }
        if (count($identities) > 1) {
            return new \Zend_Auth_Result(\Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS, NULL);
        }

        /** @var Concrete $identity */
        $identity = $identities->current();
        if (!$this->checkCredential($identity)) {
            return new \Zend_Auth_Result(\Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, NULL);
        }

        return new \Zend_Auth_Result(\Zend_Auth_Result::SUCCESS, $identity);
    }

    /**
     * @return Listing
     */
    protected function getIdentities()
    {
        $listClass = $this->getIdentityClassname() . '\\Listing';
        /** @var Listing $list */
        $list = new $listClass();
        $list->addConditionParam($this->identityColumn . ' = ?', $this->identity);

        if ($this->objectPath) {
            $list->addConditionParam('o_path LIKE ?', $this->objectPath . '%');
        }

        $list->addConditionParam('o_published = ?', 1);

        return $list;
    }

    protected function checkCredential(Concrete $identity)
    {
        /** @var \Pimcore\Model\Object\ClassDefinition\Data\Password $credentialField */
        $credentialField = $identity->getClass()->getFieldDefinition($this->credentialColumn);
        $hashed = $credentialField->getDataForResource($this->credential);

        return $hashed === $identity->{$this->credentialColumn};
    }
}