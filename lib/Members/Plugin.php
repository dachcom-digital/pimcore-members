<?php

namespace Members;

use Pimcore\API\Plugin as PluginLib;

use Members\Plugin\Install;
use Members\Model\Configuration;
use Members\Model\Member;
use Members\RestrictionService;
use Pimcore\Model\Object;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{
    /**
     * @var \Zend_Translate
     */
    protected static $_translate;

    /**
     * Plugin constructor.
     *
     * @param null $jsPaths
     * @param null $cssPaths
     * @param null $alternateIndexDir
     */
    public function __construct($jsPaths = NULL, $cssPaths = NULL, $alternateIndexDir = NULL)
    {
        parent::__construct($jsPaths, $cssPaths);

        define('MEMBERS_PATH', PIMCORE_PLUGINS_PATH . '/Members');
        define('MEMBERS_INSTALL_PATH', MEMBERS_PATH . '/install');
        define('MEMBERS_PLUGIN_CONFIG', MEMBERS_PATH . '/plugin.xml');
    }

    /**
     *
     */
    public function init()
    {
        parent::init();

        \Zend_Controller_Action_HelperBroker::addPrefix('Members_Controller_Action_Helper');

        \Pimcore::getEventManager()->attach('system.startup', [$this, 'registerPluginController']);

        \Pimcore::getEventManager()->attach('document.preDelete', [$this, 'handleDocumentDeletion']);
        \Pimcore::getEventManager()->attach('object.preDelete', [$this, 'handleObjectDeletion']);
        \Pimcore::getEventManager()->attach('asset.preDelete', [$this, 'handleAssetDeletion']);

        \Pimcore::getEventManager()->attach(['object.postAdd','object.postUpdate'], [$this, 'handleObjectAddUpdate']);
        \Pimcore::getEventManager()->attach(['document.postAdd','document.postUpdate'], [$this, 'handleDocumentAddUpdate']);
        \Pimcore::getEventManager()->attach(['asset.postAdd','asset.postUpdate'], [$this, 'handleAssetAddUpdate']);

        \Pimcore::getEventManager()->attach('members.register.validate', ['Members\Events\Register', 'validate']);
        \Pimcore::getEventManager()->attach('members.update.validate', ['Members\Events\Update', 'validate']);
        \Pimcore::getEventManager()->attach('members.password.reset', ['Members\Events\Password', 'reset']);
        \Pimcore::getEventManager()->attach('members.password.change', ['Members\Events\Password', 'change']);

        if (Configuration::get('actions.postRegister') !== FALSE) {
            \Pimcore::getEventManager()->attach('members.register.post', ['Members\Events\Register', Configuration::get('actions.postRegister')]);
        }
    }

    /**
     * @param \Zend_EventManager_Event $e
     */
    public function registerPluginController(\Zend_EventManager_Event $e)
    {
        $frontController = $e->getTarget();

        if ($frontController instanceof \Zend_Controller_Front) {
            $frontController->registerPlugin(new Controller\Plugin\Frontend());
        }
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleObjectAddUpdate(\Zend_EventManager_Event $e)
    {
        $front = \Zend_Controller_Front::getInstance();

        //only trigger update if object gets moved.
        //default restriction object update gets handled by restrictionController.
        if($e->getName() === 'object.postUpdate' && $front->getRequest()->getParam('values') === NULL) {
            return FALSE;
        }

        $object = $e->getTarget();
        return RestrictionService::checkRestrictionContext($object, 'object');
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleDocumentAddUpdate(\Zend_EventManager_Event $e)
    {
        $front = \Zend_Controller_Front::getInstance();

        //only trigger update if object gets moved.
        //default restriction object update gets handled by restrictionController.
        if($e->getName() === 'document.postUpdate' && $front->getRequest()->getParam('parentId') === NULL) {
            return FALSE;
        }

        $document = $e->getTarget();
        return RestrictionService::checkRestrictionContext($document, 'page');
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleAssetAddUpdate(\Zend_EventManager_Event $e)
    {
        $front = \Zend_Controller_Front::getInstance();

        //only trigger update if object gets moved.
        //default restriction object update gets handled by restrictionController.
        if($e->getName() === 'asset.postUpdate' && $front->getRequest()->getParam('parentId') === NULL) {
            return FALSE;
        }

        $asset = $e->getTarget();
        return RestrictionService::checkRestrictionContext($asset, 'asset');
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleDocumentDeletion(\Zend_EventManager_Event $e)
    {
        $document = $e->getTarget();
        return RestrictionService::deleteRestriction($document, 'page');
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleAssetDeletion(\Zend_EventManager_Event $e)
    {
        $asset = $e->getTarget();
        return RestrictionService::deleteRestriction($asset, 'asset');
    }

    /**
     * @param \Zend_EventManager_Event $e
     *
     * @return bool
     */
    public function handleObjectDeletion(\Zend_EventManager_Event $e)
    {
        $object = $e->getTarget();
        return RestrictionService::deleteRestriction($object, 'object');
    }

    /**
     *  indicates whether this plugins is currently installed
     * @return boolean $isInstalled
     */
    public static function isInstalled()
    {
        $searchConf = Configuration::get('installed');

        if (is_null($searchConf)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @param string $language
     *
     * @return string path to the translation file relative to plugin directory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(PIMCORE_PLUGINS_PATH . '/Members/static/texts/' . $language . '.csv')) {
            return '/Members/static/texts/' . $language . '.csv';
        } else {
            return '/Members/static/texts/en.csv';
        }
    }

    /**
     *  install function
     * @return string $message statusmessage to display in frontend
     */
    public static function install()
    {
        try {
            $install = new Install();
            $install->installConfigFile();
            $install->installClasses();
            $install->installDocuments();
            $install->installFolder();
            $install->createRedirect();
            $install->installTranslations();
            $install->injectDbData();
        } catch (\Exception $e) {
            \Pimcore\Logger::crit($e);

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

        if ($success) {
            return self::getTranslate()->_('members_uninstalled_successfully');
        } else {
            return self::getTranslate()->_('members_uninstall_failed');
        }
    }

    /**
     * @param null $lang
     *
     * @return \Zend_Translate
     */
    public static function getTranslate($lang = NULL)
    {
        if (self::$_translate instanceof \Zend_Translate) {
            return self::$_translate;
        }

        if (is_null($lang)) {
            try {
                $lang = \Zend_Registry::get('Zend_Locale')->getLanguage();
            } catch (\Exception $e) {
                $lang = 'en';
            }
        }

        self::$_translate = new \Zend_Translate(

            'csv',
            PIMCORE_PLUGINS_PATH . self::getTranslationFile($lang),
            $lang,
            ['delimiter' => ',']

        );

        return self::$_translate;
    }

}