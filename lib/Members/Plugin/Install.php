<?php

namespace Members\Plugin;

use Pimcore\Model\Object;
use Pimcore\Model\User;
use Pimcore\Model\Tool\Setup;

use Members\Model\Configuration;

class Install {

    /**
     * @var User
     */
    protected $_user;

    /**
     * @var null|string
     */
    private $configFile = NULL;

    public function __construct()
    {
        $this->configFile = MEMBERS_CONFIGURATION_FILE;
    }

    public function installConfigFile()
    {
        Configuration::set('installed', TRUE);
        return TRUE;
    }

    public function injectDbData()
    {
        $setup = new Setup();
        $setup->insertDump( MEMBERS_INSTALL_PATH . '/sql/install.sql' );
    }

    public function installClasses()
    {
        $classNames = glob(MEMBERS_INSTALL_PATH . '/class-*.json');

        if( empty( $classNames ) )
        {
            return FALSE;
        }

        foreach( $classNames as $classPath )
        {
            $className = str_replace('class-','', basename($classPath, '.json') );

            $class = Object\ClassDefinition::getByName($className);

            if ( !$class )
            {
                $jsonFile = $classPath;
                $json = file_get_contents($jsonFile);

                $class = Object\ClassDefinition::create();
                $class->setName($className);
                $class->setUserOwner($this->_getUser()->getId());

                Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, true);

            }

        }

        return TRUE;
    }

    public function removeConfig()
    {
        $configFile = \Pimcore\Config::locateConfigFile('members_configurations');

        if (is_file($configFile  . '.php'))
        {
            rename($configFile  . '.php', $configFile  . '.BACKUP');
        }
    }

    /**
     * @return User
     */
    protected function _getUser()
    {
        if (!$this->_user) {
            $this->_user = \Zend_Registry::get('pimcore_admin_user');
        }
        return $this->_user;
    }
}