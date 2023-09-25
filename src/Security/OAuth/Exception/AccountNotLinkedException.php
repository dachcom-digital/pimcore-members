<?php

namespace MembersBundle\Security\OAuth\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotLinkedException extends AuthenticationException
{
    protected ?string $registrationKey = null;

    public function setRegistrationKey(string $registrationKey): void
    {
        $this->registrationKey = $registrationKey;
    }

    public function getRegistrationKey(): ?string
    {
        return $this->registrationKey;
    }
}
