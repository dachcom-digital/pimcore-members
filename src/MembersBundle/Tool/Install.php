<?php

namespace MembersBundle\Tool;

use League\Flysystem\FilesystemException;
use MembersBundle\Configuration\Configuration;
use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Db\Connection;
use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Translation;
use Pimcore\Model\User;
use Pimcore\Tool;
use Pimcore\Tool\Storage;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class Install extends SettingsStoreAwareInstaller
{
    protected TokenStorageUserResolver $resolver;
    protected DecoderInterface $serializer;
    protected Configuration $configuration;

    public function setTokenStorageUserResolver(TokenStorageUserResolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    public function setSerializer(DecoderInterface $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function install(): void
    {
        $this->installObjectFolder();
        $this->installEmails();
        $this->installFolder();
        $this->installTranslations();
        $this->installDbStructure();

        parent::install();
    }

    /**
     * @throws InstallationException
     */
    private function installObjectFolder(): void
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
            throw new InstallationException(sprintf('Failed to create members object storage. error was: "%s"', $e->getMessage()));
        }
    }

    private function installEmails(): void
    {
        $file = $this->getInstallSourcesPath() . '/emails-Members.json';
        $contents = file_get_contents($file);
        $docs = $this->serializer->decode($contents, 'json');
        $defaultLanguage = \Pimcore\Tool::getDefaultLanguage();

        if ($defaultLanguage === null) {
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

                    if (isset($def['controller'])) {
                        $document->setController($def['controller']);
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
                                        $document->setRawEditable($key, $type, $content);
                                    }
                                }
                            }
                        }
                    }

                    $document->setPublished(1);

                    try {
                        $document->save();
                    } catch (\Exception $e) {
                        throw new InstallationException(sprintf('Failed to install members email. error was: "%s"', $e->getMessage()));
                    }
                }
            }
        }
    }

    /**
     * @throws InstallationException
     */
    public function installFolder(): void
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
            throw new InstallationException(sprintf('Failed to install protected asset folder. error was: "%s"', $e->getMessage()));
        }

        $rule = 'RewriteEngine On' . "\n";
        $rule .= 'RewriteCond %{HTTP_HOST}==%{HTTP_REFERER} !^(.*?)==https?://\1/admin/ [OR]' . "\n";
        $rule .= 'RewriteCond %{HTTP_COOKIE} !^.*pimcore_admin_sid.*$ [NC]' . "\n";
        $rule .= 'RewriteRule ^ - [L,F]';

        try {
            $storage = Storage::get('asset');
            $storage->write($folder->getRealFullPath() . '/.htaccess', $rule);
        } catch (FilesystemException $e) {
            throw new InstallationException(sprintf('Error while creating .htaccess protection: %s', $e->getMessage()));
        }
    }

    /**
     * @throws InstallationException
     */
    public function installTranslations(): void
    {
        $csv = $this->getInstallSourcesPath() . '/translations/frontend.csv';
        $csvAdmin = $this->getInstallSourcesPath() . '/translations/admin.csv';

        try {
            Translation::importTranslationsFromFile($csv, Translation::DOMAIN_DEFAULT, true, Tool\Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }

        try {
            Translation::importTranslationsFromFile($csvAdmin, Translation::DOMAIN_ADMIN, true, Tool\Admin::getLanguages());
        } catch (\Exception $e) {
            throw new InstallationException(sprintf('Failed to install admin translations. error was: "%s"', $e->getMessage()));
        }
    }

    public function installDbStructure(): void
    {
        /** @var Connection $db */
        $db = \Pimcore\Db::get();
        $db->query(file_get_contents($this->getInstallSourcesPath() . '/sql/install.sql'));
    }

    protected function getUserId(): int
    {
        $userId = 0;
        $user = $this->resolver->getUser();
        if ($user instanceof User) {
            $userId = $this->resolver->getUser()->getId();
        }

        return $userId;
    }

    protected function getInstallSourcesPath(): string
    {
        return __DIR__ . '/../Resources/install';
    }
}
