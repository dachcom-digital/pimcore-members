<?php

namespace MembersBundle\Tool;

use Pimcore\Cache\Runtime;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;

use Pimcore\Model\Tool\Setup;
use Pimcore\Tool;
use Pimcore\Model\Document;
use Pimcore\Model\Object;
use Pimcore\Model\Asset;
use Pimcore\Model\Translation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Serializer;
use MembersBundle\Configuration\Configuration;

class Install extends AbstractInstaller
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $installSourcesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var User
     */
    protected $_user;

    /**
     * Install constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        parent::__construct();

        $this->serializer = $serializer;
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->copyConfigFiles();

        // @fixme: manage routing and documents?
        // @see https://github.com/pimcore/pimcore/issues/1733
        $this->installObjectFolder();
        $this->installEmails();
        $this->installFolder();
        $this->installTranslations();
        $this->injectDbData();

        return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->rename(
                Configuration::SYSTEM_CONFIG_FILE_PATH,
                PIMCORE_PRIVATE_VAR . '/bundles/MembersBundle/config_backup.yml'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeInstalled()
    {
        return !$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUninstalled()
    {
        return $this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH);
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return FALSE;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUpdated()
    {
        return FALSE;
    }

    /**
     * copy sample config file - if not exists.
     */
    private function copyConfigFiles()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->copy(
                $this->installSourcesPath . '/config.yml',
                Configuration::SYSTEM_CONFIG_FILE_PATH
            );
        }

    }

    private function installObjectFolder()
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
    }

    private function installEmails()
    {
        $file = $this->installSourcesPath . '/emails-Members.json';
        $contents = file_get_contents($file);
        $docs = $this->serializer->decode($contents, 'json');

        $defaultLanguage = \Pimcore\Config::getSystemConfig()->general->defaultLanguage;

        if(!in_array($defaultLanguage, ['de', 'en'])) {
            $defaultLanguage = 'en';
        }

        foreach ($docs as $def) {

            $path = '/' . $def['path'] . '/' . $def['key'];

            if (!Document\Service::pathExists($path)) {
                $class = "Pimcore\\Model\\Document\\" . ucfirst($def['type']);

                if (\Pimcore\Tool::classExists($class)) {
                    /** @var Document\Email $document */
                    $document = new $class();
                    $document->setParent(Document::getByPath('/' . $def['path']));
                    $document->setKey($def['key']);
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
                            if ($fieldLanguage !== $defaultLanguage) {
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

                }
            }
        }
    }

    /**
     * @return bool
     */
    public function installFolder()
    {
        $folderName = 'restricted-assets';

        if (Asset\Folder::getByPath('/' . $folderName) instanceof Asset\Folder) {
            return FALSE;
        }

        $folder = new Asset\Folder();
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
     *
     */
    public function installTranslations()
    {
        $csv = $this->installSourcesPath . '/translations/data.csv';
        $csvAdmin = $this->installSourcesPath . '/translations/admin/data.csv';
        Translation\Website::importTranslationsFromFile($csv, TRUE, Tool\Admin::getLanguages());
        Translation\Admin::importTranslationsFromFile($csvAdmin, TRUE, Tool\Admin::getLanguages());
    }

    /**
     *
     */
    public function injectDbData()
    {
        $setup = new Setup();
        $setup->insertDump($this->installSourcesPath . '/sql/install.sql');
    }

    /**
     * @return User
     */
    protected function _getUser()
    {
        if (!$this->_user) {
            $this->_user = Runtime::get('pimcore_admin_user');
        }

        return $this->_user;
    }

}