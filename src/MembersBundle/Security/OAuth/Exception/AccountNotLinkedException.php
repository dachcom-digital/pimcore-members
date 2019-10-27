<?php

namespace MembersBundle\Security\OAuth\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotLinkedException extends AuthenticationException
{
    /**
     * @var string
     */
    protected $registrationKey;

    /**
     * @param string $registrationKey
     */
    public function setRegistrationKey(string $registrationKey)
    {
        $this->registrationKey = $registrationKey;
    }

    /**
     * @return string
     */
    public function getRegistrationKey()
    {
        return $this->registrationKey;
    }
}
