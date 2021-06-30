<?php

namespace MembersBundle\Registry;

use MembersBundle\Security\OAuth\Dispatcher\LoginProcessor\LoginProcessorInterface;

class OAuthLoginProcessorRegistry implements OAuthLoginProcessorRegistryInterface
{
    protected array $processor;

    public function register(LoginProcessorInterface $service, string $identifier)
    {
        $this->processor[$identifier] = $service;
    }

    public function has(string $identifier): bool
    {
        return isset($this->processor[$identifier]);
    }


    public function get(string $identifier): LoginProcessorInterface
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Data Provider does not exist');
        }

        return $this->processor[$identifier];
    }
}
