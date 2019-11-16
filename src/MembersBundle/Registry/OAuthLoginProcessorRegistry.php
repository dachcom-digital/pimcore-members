<?php

namespace MembersBundle\Registry;

use MembersBundle\Security\OAuth\Dispatcher\LoginProcessor\LoginProcessorInterface;

class OAuthLoginProcessorRegistry implements OAuthLoginProcessorRegistryInterface
{
    /**
     * @var array
     */
    protected $processor;

    /**
     * @param LoginProcessorInterface $service
     * @param string                  $identifier
     */
    public function register($service, $identifier)
    {
        if (!in_array(LoginProcessorInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf('%s needs to implement "%s", "%s" given.', get_class($service), LoginProcessorInterface::class, implode(', ', class_implements($service)))
            );
        }

        $this->processor[$identifier] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function has($identifier)
    {
        return isset($this->processor[$identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" Data Provider does not exist');
        }

        return $this->processor[$identifier];
    }
}
