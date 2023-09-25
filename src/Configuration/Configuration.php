<?php

namespace MembersBundle\Configuration;

class Configuration
{
    protected array $config;

    public function setConfig(array $config = []): void
    {
        $this->config = $config;
    }

    public function getConfigArray(): array
    {
        return $this->config;
    }

    public function getConfig(string $slot): mixed
    {
        return $this->config[$slot];
    }

    public function getOAuthConfig(string $slot): mixed
    {
        return $this->config['oauth'][$slot];
    }
}
