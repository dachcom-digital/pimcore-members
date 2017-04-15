<?php

namespace Members\Plugin;

use Pimcore\Model\Object;
use Pimcore\Model\Document;
use Pimcore\Model\User;
use Pimcore\Model\Tool\Setup;
use Pimcore\Model\Translation\Website;
use Pimcore\Tool;
use Members\Model\Configuration;

class Install
{
    /**
     * @var User
     */
    protected $_user;

    /**
     * Install constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return bool
     */
    public function installConfigFile()
    {
        Configuration::set('installed', TRUE);
        Configuration::set('sendNotificationMailAfterConfirm', FALSE);

        Configuration::set('core.settings.object.allowed', []);

        Configuration::set('auth.adapter.identityClassname', 'Object\Member');
        Configuration::set('auth.adapter.identityColumn', 'email');
        Configuration::set('auth.adapter.credentialColumn', 'password');
        Configuration::set('auth.adapter.objectPath', '/members');

        Configuration::set('routes.login', '/%lang/members/login');
        Configuration::set('routes.logout', '/%lang/members/logout');
        Configuration::set('routes.register', '/%lang/members/register');
        Configuration::set('routes.profile', '/%lang/members');
        Configuration::set('routes.profile.update', '/%lang/members/update-profile');
        Configuration::set('routes.profile.changePassword', '/%lang/members/password-change');
        Configuration::set('routes.confirm', '/%lang/members/confirm');
        Configuration::set('routes.passwordRequest', '/%lang/members/password-request');
        Configuration::set('routes.passwordReset', '/%lang/members/password-reset');
        Configuration::set('routes.login.redirectAfterSuccess', '/%lang/members');
        Configuration::set('routes.login.redirectAfterRefusal', '/%lang/members/refused');

        Configuration::set('emails.registerConfirm', '/%lang/members/emails/register-confirm');
        Configuration::set('emails.registerNotification', '/%lang/members/emails/register-notification');
        Configuration::set('emails.passwordReset', '/%lang/members/emails/password-reset');

        Configuration::set('actions.postRegister', 'confirm');

        return TRUE;
    }

    /**
     *
     */
    public function installTranslations()
    {

        $csv = PIMCORE_PLUGINS_PATH . '/Members/install/translations/data.csv';
        Website::importTranslationsFromFile($csv, TRUE, Tool\Admin::getLanguages());
    }

    /**
     *
     */
    public function injectDbData()
    {
        $setup = new Setup();
        $setup->insertDump(MEMBERS_INSTALL_PATH . '/sql/install.sql');
    }

    /**
     * @return bool
     */
    public function installClasses()
    {
        $classNames = glob(MEMBERS_INSTALL_PATH . '/class-*.json');

        if (empty($classNames)) {
            return FALSE;
        }

        foreach ($classNames as $classPath) {
            $className = str_replace('class-', '', basename($classPath, '.json'));

            $class = Object\ClassDefinition::getByName($className);

            if (!$class) {
                $jsonFile = $classPath;
                $json = file_get_contents($jsonFile);

                $class = Object\ClassDefinition::create();
                $class->setName($className);
                $class->setUserOwner($this->_getUser()->getId());

                Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, TRUE);
            }
        }

        return TRUE;
    }

    /**
     *
     */
    public function installDocuments()
    {
        //install object folder "members" and lock it!
        $membersPath = Object\Folder::getByPath('/members');

        if (!$membersPath instanceof Object\Folder) {
            $obj = Object\Folder::create(
                [
                    'o_parentId'         => 1,
                    'o_creationDate'     => time(),
                    'o_userOwner'        => $this->_getUser()->getId(),
                    'o_userModification' => $this->_getUser()->getId(),
                    'o_key'              => 'members',
                    'o_published'        => TRUE
                ]
            );

            $obj->setLocked(TRUE);
            $obj->update();
        }

        $file = PIMCORE_PLUGINS_PATH . '/Members/install/documents-Members.json';
        $docs = new \Zend_Config_Json($file);

        $validLanguages = explode(",", \Pimcore\Config::getSystemConfig()->general->validLanguages);
        $languagesDone = [];

        foreach ($validLanguages as $language) {
            $languageDocument = Document::getByPath("/" . $language);

            if (!$languageDocument instanceof Document) {
                $languageDocument = new Document\Page();
                $languageDocument->setParent(Document::getById(1));
                $languageDocument->setKey($language);
                $languageDocument->save();
            }

            foreach ($docs as $def) {
                $def = $def->toArray();

                $path = "/" . $language . "/" . $def['path'] . "/" . $def['key'];

                if (!Document\Service::pathExists($path)) {
                    $class = "Pimcore\\Model\\Document\\" . ucfirst($def['type']);

                    if (\Pimcore\Tool::classExists($class)) {
                        /** @var Document $doc */
                        $document = new $class();
                        $document->setParent(Document::getByPath("/" . $language . "/" . $def['path']));

                        $document->setKey($def['key']);
                        $document->setProperty("language", $language, 'text', TRUE);

                        $document->setUserOwner($this->_getUser()->getId());
                        $document->setUserModification($this->_getUser()->getId());

                        if (isset($def['name'])) {
                            $document->setName($def['name']);
                        }
                        if (isset($def['title'])) {
                            $document->setTitle($def['title']);
                        }
                        if (isset($def['module'])) {
                            $document->setModule($def['module']);
                        }
                        if (isset($def['controller'])) {
                            $document->setController($def['controller']);
                        }
                        if (isset($def['action'])) {
                            $document->setAction($def['action']);
                        }
                        if (isset($def['template'])) {
                            $document->setTemplate($def['template']);
                        }

                        if (array_key_exists('data', $def)) {
                            foreach ($def['data'] as $fieldLanguage => $fields) {
                                if ($fieldLanguage !== $language) {
                                    continue;
                                }

                                foreach ($fields as $field) {
                                    $key = $field['key'];
                                    $type = $field['type'];
                                    $content = NULL;

                                    if (array_key_exists('value', $field)) {
                                        $content = $field['value'];
                                    }

                                    if (!empty($content)) {
                                        if ($type === 'objectProperty') {
                                            $document->setValue($key, $content);
                                        } else {
                                            $document->setRawElement($key, $type, $content);
                                        }
                                    }
                                }
                            }
                        }

                        $document->setPublished(TRUE);
                        $document->save();

                        //Link translations
                        foreach ($languagesDone as $doneLanguage) {
                            $translatedDocument = Document::getByPath("/" . $doneLanguage . "/" . $def['path'] . "/" . $def['key']);

                            if ($translatedDocument) {
                                $service = new \Pimcore\Model\Document\Service();
                                $service->addTranslation($document, $translatedDocument, $doneLanguage);
                            }
                        }
                    }
                }
            }

            $languagesDone[] = $language;
        }
    }

    /**
     * @return bool
     */
    public function installFolder()
    {

        $folderName = 'restricted-assets';

        if (\Pimcore\Model\Asset\Folder::getByPath('/' . $folderName) instanceof \Pimcore\Model\Asset\Folder) {
            return FALSE;
        }

        $folder = new \Pimcore\Model\Asset\Folder();
        $folder->setCreationDate(time());
        $folder->setLocked(TRUE);
        $folder->setUserOwner(1);
        $folder->setUserModification(0);
        $folder->setParentId(1);
        $folder->setFilename($folderName);
        $folder->save();

        //now create .htaccess file to disallow every request to this folder (exept admin!
        $f = fopen(PIMCORE_ASSET_DIRECTORY . $folder->getFullPath() . '/.htaccess', 'a+');

        $rule = 'RewriteEngine On' . "\n";
        $rule .= 'RewriteCond %{HTTP_HOST}==%{HTTP_REFERER} !^(.*?)==https?://\1/admin/ [OR]' . "\n";
        $rule .= 'RewriteCond %{HTTP_COOKIE} !^.*pimcore_admin_sid.*$ [NC]' . "\n";
        $rule .= 'RewriteRule ^ - [L,F]';

        fwrite($f, $rule);
        fclose($f);
    }

    /**
     * @return bool
     */
    public function createRedirect()
    {
        $redirect = new \Pimcore\Model\Redirect();
        $redirect->setValues(['source' => '@^/members/request\-data/(.*)$@', 'target' => '/plugin/Members/request/serve/?d=$1', 'statusCode' => 302, 'priority' => 1]);
        $redirect->save();

        return TRUE;
    }

    /**
     *
     */
    public function removeConfig()
    {
        $configFile = \Pimcore\Config::locateConfigFile('members_configurations');

        if (is_file($configFile . '.php')) {
            rename($configFile . '.php', $configFile . '.BACKUP');
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