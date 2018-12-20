<?php

namespace MembersBundle\Configuration;

use Pimcore\Extension\Bundle\PimcoreBundleManager;
use Symfony\Component\EventDispatcher\GenericEvent;

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
     * @return array
     */
    public function getConfigNode()
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public function getConfigArray()
    {
        return $this->config;
    }

    /**
     * @param $slot
     *
     * @return mixed
     */
    public function getConfig($slot)
    {
        return $this->config[$slot];
    }

    public function getLocalizedPath($slot, $locale = null)
    {
        $data = $this->getConfig($slot);

        $event = new GenericEvent($this, [
            'route' => $data
        ]);

        \Pimcore::getEventDispatcher()->dispatch(
            'members.path.route',
            $event
        );

        if ($event->hasArgument('url')) {
            $url = $event->getArgument('url');
        } else {
            $lang = '';
            if (!empty($locale)) {
                $lang = (string)$locale;
            }

            $url = str_replace('/%lang', '/' . $lang, $data);
        }

        return $url;
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