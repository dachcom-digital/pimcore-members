<?php

namespace Members;

use Pimcore\API\Plugin as PluginLib;

use Members\Plugin\Install;
use Members\Model\Configuration;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    /**
     * @var \Zend_Translate
     */
    protected static $_translate;

    public function __construct($jsPaths = null, $cssPaths = null, $alternateIndexDir = null)
    {
        parent::__construct($jsPaths, $cssPaths);

        define('MEMBERS_PATH', PIMCORE_PLUGINS_PATH . '/Members');
        define('MEMBERS_INSTALL_PATH', MEMBERS_PATH . '/install');
        define('MEMBERS_CONFIGURATION_FILE', PIMCORE_CONFIGURATION_DIRECTORY . '/members_configuration.php');

    }

    public function init()
    {
        parent::init();
    }

    /**
     *  indicates whether this plugins is currently installed
     * @return boolean $isInstalled
     */
    public static function isInstalled()
    {
        $searchConf = Configuration::get('installed');

        if( is_null( $searchConf ) )
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     *
     * @param string $language
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(PIMCORE_PLUGINS_PATH . '/Members/static/texts/' . $language . '.csv'))
        {
            return '/Members/static/texts/' . $language . '.csv';
        }
        else
        {
            return '/Members/static/texts/en.csv';
        }
    }

    /**
     *  install function
     * @return string $message statusmessage to display in frontend
     */
    public static function install()
    {
        try
        {
            $install = new Install();
            $install->installConfigFile();
            $install->installClasses();
            $install->injectDbData();

        }
        catch (\Exception $e)
        {
            \Logger::crit($e);
            return self::getTranslate()->_('members_install_failed');
        }

        return self::getTranslate()->_('members_install_successfully');

    }

    /**
     * uninstall function
     * @return string $message status message to display in frontend
     */
    public static function uninstall()
    {
        $install = new Install();
        $install->removeConfig();

        $success = TRUE;

        if ($success)
        {
            return self::getTranslate()->_('members_uninstalled_successfully');
        }
        else
        {
            return self::getTranslate()->_('members_uninstall_failed');
        }

    }

    /**
     * @return \Zend_Translate
     */
    public static function getTranslate($lang = null)
    {
        if (self::$_translate instanceof \Zend_Translate) {
            return self::$_translate;
        }
        if(is_null($lang)) {
            try {
                $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
            } catch (\Exception $e) {
                $lang = 'en';
            }
        }

        self::$_translate = new \Zend_Translate(
            'csv',
            PIMCORE_PLUGINS_PATH .self::getTranslationFile($lang),
            $lang,
            array('delimiter' => ',')
        );
        return self::$_translate;
    }

}