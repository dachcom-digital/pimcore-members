<?php

namespace MembersBundle\Tool;

use MembersBundle\Configuration\Configuration;
use MembersBundle\MembersBundle;
use PackageVersions\Versions;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Tool\Setup;
use Pimcore\Model\Translation;
use Pimcore\Model\User;
use Pimcore\Tool;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Yaml\Yaml;

class Install extends AbstractInstaller
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var TokenStorageUserResolver
     */
    protected $resolver;

    /**
     * @var DecoderInterface
     */
    protected $serializer;

    /**
     * @var string
     */
    private $installSourcesPath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * Install constructor.
     *
     * @param Configuration            $configuration
     * @param TokenStorageUserResolver $resolver
     * @param DecoderInterface         $serializer
     */
    public function __construct(Configuration $configuration, TokenStorageUserResolver $resolver, DecoderInterface $serializer)
    {
        parent::__construct();

        $this->configuration = $configuration;
        $this->resolver = $resolver;
        $this->serializer = $serializer;
        $this->installSourcesPath = __DIR__ . '/../Resources/install';
        $this->fileSystem = new Filesystem();
        $this->currentVersion = Versions::getVersion(MembersBundle::PACKAGE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->installOrUpdateConfigFile();

        // @fixme: manage routing and documents?
        // @see https://github.com/pimcore/pimcore/issues/1733
        $this->installObjectFolder();
        $this->installEmails();
        $this->installFolder();
        $this->installTranslations();
        $this->injectDbData();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        $this->installOrUpdateConfigFile();
        $this->installTranslations();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $this->fileSystem->remove(
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
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeUpdated()
    {
        $needUpdate = false;
        if ($this->fileSystem->exists(Configuration::SYSTEM_CONFIG_FILE_PATH)) {
            $config = Yaml::parse(file_get_contents(Configuration::SYSTEM_CONFIG_FILE_PATH));
            if ($config['version'] !== $this->currentVersion) {
                $needUpdate = true;
            }
        }

        return $needUpdate;
    }

    /**
     * install / update config file.
     */
    private function installOrUpdateConfigFile()
    {
        if (!$this->fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH)) {
            $this->fileSystem->mkdir(Configuration::SYSTEM_CONFIG_DIR_PATH);
        }

        $config = ['version' => $this->currentVersion];
        $yml = Yaml::dump($config);
        file_put_contents(Configuration::SYSTEM_CONFIG_FILE_PATH, $yml);
    }

    /**
     * Create locked members object folder
     */
    private function installObjectFolder()
    {
        // install object folder and lock it!
        $storagePath = $this->configuration->getConfig('user')['storage_path'];
        if (empty($storagePath) || DataObject\Service::pathExists($storagePath)) {
            return false;
        }

        DataObject\Service::createFolderByPath($storagePath, ['locked' => true]);
    }

    /**
     * Install preconfigured Emails
     */
    private function installEmails()
    {
        $file = $this->installSourcesPath . '/emails-Members.json';
        $contents = file_get_contents($file);
        $docs = $this->serializer->decode($contents, 'json');

        $defaultLanguage = \Pimcore\Config::getSystemConfig()->general->defaultLanguage;

        if (!in_array($defaultLanguage, ['de', 'en'])) {
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
                    $document->setUserOwner($this->getUserId());
                    $document->setUserModification($this->getUserId());

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
                                $content = null;

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

                    $document->setPublished(1);
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
            return false;
        }

        $folder = new Asset\Folder();
        $folder->setCreationDate(time());
        $folder->setLocked(true);
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
     * Install Translations for Website and Admin
     */
    public function installTranslations()
    {
        $csv = $this->installSourcesPath . '/translations/data.csv';
        $csvAdmin = $this->installSourcesPath . '/translations/admin/data.csv';
        Translation\Website::importTranslationsFromFile($csv, true, Tool\Admin::getLanguages());
        Translation\Admin::importTranslationsFromFile($csvAdmin, true, Tool\Admin::getLanguages());
    }

    public function injectDbData()
    {
        $setup = new Setup();
        $setup->insertDump($this->installSourcesPath . '/sql/install.sql');
    }

    /**
     * @return int
     */
    protected function getUserId()
    {
        $userId = 0;
        $user = $this->resolver->getUser();
        if ($user instanceof User) {
            $userId = $this->resolver->getUser()->getId();
        }

        return $userId;
    }

}