<?php

namespace MembersBundle\Registry;

use MembersBundle\Security\OAuth\Dispatcher\LoginProcessor\LoginProcessorInterface;

interface OAuthLoginProcessorRegistryInterface
{
    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier);

    /**
     * @param string $identifier
     *
     * @return LoginProcessorInterface
     *
     * @throws \Exception
     */
    public function get($identifier);
}
