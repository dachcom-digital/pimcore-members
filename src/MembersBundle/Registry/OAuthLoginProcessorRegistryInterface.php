<?php

namespace MembersBundle\Registry;

use MembersBundle\Security\OAuth\Dispatcher\LoginProcessor\LoginProcessorInterface;

interface OAuthLoginProcessorRegistryInterface
{
    public function has(string $identifier): bool;

    /**
     * @throws \Exception
     */
    public function get(string $identifier): LoginProcessorInterface;
}
