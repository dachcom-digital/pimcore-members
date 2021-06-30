<?php

namespace MembersBundle\Registry;

use MembersBundle\Security\OAuth\Dispatcher\LoginProcessor\LoginProcessorInterface;

interface OAuthLoginProcessorRegistryInterface
{
    public function has(string $identifier): bool;

    public function get(string $identifier): LoginProcessorInterface;
}
