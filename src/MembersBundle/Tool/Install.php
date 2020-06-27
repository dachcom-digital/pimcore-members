<?php

namespace MembersBundle\Tool;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\MigrationException;
use Doctrine\DBAL\Migrations\Version;
use MembersBundle\Configuration\Configuration;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Db\Connection;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Migrations\Migration\InstallMigration;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Translation;
use Pimcore\Model\User;
use Pimcore\Tool;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class Install extends MigrationInstaller
{
    /**
     * @var TokenStorageUserResolver
     */
    protected $resolver;

    /**
     * @var DecoderInterface
     */
    protected $serializer;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param TokenStorageUserResolver $resolver
     */
    public function setTokenStorageUserResolver(TokenStorageUserResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param DecoderInterface $serializer
     */
    public function setSerializer(DecoderInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion(): string
    {
        return '00000001';
    }

    /**
     * @throws AbortMigrationException
     * @throws MigrationException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function beforeInstallMigration()
    {
        $markVersionsAsMigrated = true;

        // legacy:
        //   we switched from config to migration
        //   if config.yml exists, this instance needs to migrate
        //   so every migration needs to run.
        // fresh:
        //   skip all versions since they are not required anymore
        //   (fresh installation does not require any version migrations)
        $fileSystem = new Filesystem();
        if ($fileSystem->exists(Configuration::SYSTEM_CONFIG_DIR_PATH . '/config.yml')) {
            $markVersionsAsMigrated = false;
        }

        if ($markVersionsAsMigrated === true) {
            $migrationConfiguration = $this->migrationManager->getBundleConfiguration($this->bundle);
            $this->migrationManager->markVersionAsMigrated($migrationConfiguration->getVersion($migrationConfiguration->getLatestVersion()));
        }

        $this->initializeFreshSetup();
    }

    /**
     * @param Schema  $schema
     * @param Version $version
     */
    public function migrateInstall(Schema $schema, Version $version)
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();
        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping installation');

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * @throws AbortMigrationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initializeFreshSetup()
    {
        $this->installObjectFolder();
        $this->installEmails();
        $this->installFolder();
        $this->installTranslations();
        $this->installDbStructure();
    }

    /**
     * @param Schema  $schema
     * @param Version $version
     */
    public function migrateUninstall(Schema $schema, Version $version)
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();
        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping uninstallation');

            return;
        }

        // currently nothing to do.
    }

    /**
     * @param string|null $version
     *
     * @throws AbortMigrationException
     */
    protected function beforeUpdateMigration(string $version = null)
    {
        $this->installTranslations();
    }

    /**
     * @throws AbortMigrationException
     */
    private function installObjectFolder()
    {
        //install object folder "/members" and lock it!
        $storagePath = $this->configuration->getConfig('storage_path');
        $membersPath = DataObject\Folder::getByPath($storagePath);

        if ($membersPath instanceof DataObject\Folder) {
            return;
        }

        try {
            DataObject\Service::createFolderByPath($storagePath, ['locked' => true]);
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to create members object storage. error was: "%s"', $e->getMessage()));
        }
    }

    /**
     * @throws AbortMigrationException
     */
    private function installEmails()
    {
        $file = $this->getInstallSourcesPath() . '/emails-Members.json';
        $contents = file_get_contents($file);
        $docs = $this->serializer->decode($contents, 'json');

        try {
            $defaultLanguage = \Pimcore\Config::getSystemConfig()->general->defaultLanguage;
        } catch (\Exception $e) {
            $defaultLanguage = 'en';
        }

        if (!in_array($defaultLanguage, ['de', 'en'])) {
            $defaultLanguage = 'en';
        }

        foreach ($docs as $def) {
            $path = '/' . $def['path'] . '/' . $def['key'];

            if (!Document\Service::pathExists($path)) {
                $class = 'Pimcore\\Model\\Document\\' . ucfirst($def['type']);

                if (\Pimcore\Tool::classExists($class)) {
                    /** @var Document\Email $document */
                    $document = new $class();
                    $document->setParent(Document::getByPath('/' . $def['path']));
                    $document->setKey($def['key']);
                    $document->setUserOwner($this->getUserId());
                    $document->setUserModification($this->getUserId());

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

                    try {
                        $document->save();
                    } catch (\Exception $e) {
                        throw new AbortMigrationException(sprintf('Failed to install members email. error was: "%s"', $e->getMessage()));
                    }
                }
            }
        }
    }

    /**
     * @throws AbortMigrationException
     */
    public function installFolder()
    {
        $folderName = 'restricted-assets';

        if (Asset\Folder::getByPath('/' . $folderName) instanceof Asset\Folder) {
            return;
        }

        $folder = new Asset\Folder();
        $folder->setCreationDate(time());
        $folder->setLocked(true);
        $folder->setUserOwner(1);
        $folder->setUserModification(0);
        $folder->setParentId(1);
        $folder->setFilename($folderName);

        try {
            $folder->save();
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to install protected asset folder. error was: "%s"', $e->getMessage()));
        }

        //now create .htaccess file to disallow every request to this folder (except admin)!
        $f = fopen(PIMCORE_ASSET_DIRECTORY . $folder->getFullPath() . '/.htaccess', 'w');

        $rule = 'RewriteEngine On' . "\n";
        $rule .= 'RewriteCond %{HTTP_HOST}==%{HTTP_REFERER} !^(.*?)==https?://\1/admin/ [OR]' . "\n";
        $rule .= 'RewriteCond %{HTTP_COOKIE} !^.*pimcore_admin_sid.*$ [NC]' . "\n";
        $rule .= 'RewriteRule ^ - [L,F]';

        fwrite($f, $rule);
        fclose($f);
    }

    /**
     * @throws AbortMigrationException
     */
    public function installTranslations()
    {
        $csv = $this->getInstallSourcesPath() . '/translations/frontend.csv';
        $csvAdmin = $this->getInstallSourcesPath() . '/translations/admin.csv';

        try {
            Translation\Website::importTranslationsFromFile($csv, true, Tool\Admin::getLanguages());
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }

        try {
            Translation\Admin::importTranslationsFromFile($csvAdmin, true, Tool\Admin::getLanguages());
        } catch (\Exception $e) {
            throw new AbortMigrationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function installDbStructure()
    {
        /** @var Connection $db */
        $db = \Pimcore\Db::get();
        $db->query(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
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

    /**
     * @return string
     */
    protected function getInstallSourcesPath()
    {
        return __DIR__ . '/../Resources/install';
    }
}
