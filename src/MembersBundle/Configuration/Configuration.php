<?php

namespace MembersBundle\Configuration;

use Pimcore\Extension\Bundle\PimcoreBundleManager;

class Configuration
{
    const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/MembersBundle';

    protected array $config = [];
    protected PimcoreBundleManager $bundleManager;

    public function __construct(PimcoreBundleManager $bundleManager)
    {
        $this->bundleManager = $bundleManager;
    }

    public function setConfig(array $config = []): void
    {
        $this->config = $config;
    }

    public function getConfigArray(): array
    {
        return $this->config;
    }

    public function getConfig(string $slot)
    {
        return $this->config[$slot];
    }

    public function getOAuthConfig(string $slot)
    {
        return $this->config['oauth'][$slot];
    }

    public function hasBundle(string $bundleName = 'ExtensionBundle\ExtensionBundle'): bool
    {
        try {
            $hasExtension = $this->bundleManager->isEnabled($bundleName);
        } catch (\Exception $e) {
            $hasExtension = false;
        }

        return $hasExtension;
    }
}
