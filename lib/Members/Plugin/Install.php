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

    public function __construct()
    {
    }

    public function installConfigFile()
    {
        Configuration::set('installed', TRUE);
        Configuration::set('auth.adapter.identityClassname', 'Object\Member');
        Configuration::set('auth.adapter.identityColumn', 'email');
        Configuration::set('auth.adapter.credentialColumn', 'password');
        Configuration::set('auth.adapter.objectPath', '/members');

        Configuration::set('routes.login', '/members/login');
        Configuration::set('routes.logout', '/members/logout');
        Configuration::set('routes.register', '/members/register');
        Configuration::set('routes.profile', '/members');
        Configuration::set('routes.confirm', '/members/confirm');
        Configuration::set('routes.passwordRequest', '/members/password-request');
        Configuration::set('routes.passwordReset', '/members/password-reset');

        Configuration::set('emails.registerConfirm', '/members/emails/register-confirm');
        Configuration::set('emails.passwordReset', '/members/emails/password-reset');

        Configuration::set('actions.postRegister', 'active');

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