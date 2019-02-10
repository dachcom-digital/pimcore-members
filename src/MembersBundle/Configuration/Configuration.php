<?php

namespace MembersBundle\Configuration;

use Pimcore\Extension\Bundle\PimcoreBundleManager;

class Configuration
{
    const SYSTEM_CONFIG_DIR_PATH = PIMCORE_PRIVATE_VAR . '/bundles/MembersBundle';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PimcoreBundleManager
     */
    protected $bundleManager;

    /**
     * Configuration constructor.
     *
     * @param PimcoreBundleManager $bundleManager
     */
    public function __construct(PimcoreBundleManager $bundleManager)
    {
        $this->bundleManager = $bundleManager;
    }

    /**
     * @param array $config
     */
    public function setConfig($config = [])
    {
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getConfigArray()
    {
        return $this->config;
    }

    /**
     * @param string $slot
     *
     * @return mixed
     */
    public function getConfig($slot)
    {
        return $this->config[$slot];
    }

    /**
     * @param string $bundleName
     *
     * @return bool
     */
    public function hasBundle($bundleName = 'ExtensionBundle\ExtensionBundle')
    {
        try {
            $hasExtension = $this->bundleManager->isEnabled($bundleName);
        } catch (\Exception $e) {
            $hasExtension = false;
        }

        return $hasExtension;
    }
}