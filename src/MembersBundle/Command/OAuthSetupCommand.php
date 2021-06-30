<?php

namespace MembersBundle\Command;

use MembersBundle\Adapter\Sso\SsoIdentityInterface;
use MembersBundle\Adapter\User\AbstractSsoAwareUser;
use MembersBundle\Adapter\User\AbstractUser;
use MembersBundle\Adapter\User\SsoAwareUserInterface;
use MembersBundle\Manager\ClassManagerInterface;
use MembersBundle\Tool\ClassInstaller;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class OAuthSetupCommand extends Command
{
    protected bool $oauthEnabled;
    protected ClassManagerInterface $classManager;
    protected ClassInstaller $classInstaller;

    public function __construct(bool $oauthEnabled, ClassManagerInterface $classManager, ClassInstaller $classInstaller)
    {
        parent::__construct();

        $this->oauthEnabled = $oauthEnabled;
        $this->classManager = $classManager;
        $this->classInstaller = $classInstaller;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('members:oauth:setup')
            ->setDescription('This command helps you the enhance Members with oauth2 connectors.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('');

        if ($this->checkSsoIdentityClassStep($input, $output) === false) {
            return 0;
        }

        if ($this->checkSsoIdentityRelationStep($input, $output) === false) {
            return 0;
        }

        if ($this->checkSsoIdentityAwareUserClass($input, $output) === false) {
            return 0;
        }

        if ($this->checkInstalledBundlesStep($input, $output) === false) {
            return 0;
        }

        if ($this->checkInstalledStep($input, $output) === false) {
            return 0;
        }

        $docLink = 'https://github.com/dachcom-digital/pimcore-members/blob/master/docs/SSO/10_Overview.md';

        $output->writeln('');
        $output->writeln('----------');

        $output->writeln(sprintf('<info>√ Congratulations! Members has been successfully configured.</info>'));
        $output->writeln(sprintf('  Please checkout our documentation to learn more about configuring OAuth2 connectors: (%s)', $docLink));

        $output->writeln('----------');
        $output->writeln('');

        return 0;
    }

    protected function checkInstalledBundlesStep(InputInterface $input, OutputInterface $output): bool
    {
        $oauthInstalled = class_exists('\\KnpU\\OAuth2ClientBundle\\Client\\ClientRegistry');

        if ($oauthInstalled === true) {
            $output->writeln(sprintf('<info>√ knpuniversity/oauth2-client-bundle is installed</info>'));

            return true;
        }

        $command = '$ composer require knpuniversity/oauth2-client-bundle:^1.0';

        $output->writeln(sprintf('<error>x</error> <question>knpuniversity/oauth2-client-bundle is not installed.</question> Please add it via composer: %s', $command));

        return false;
    }

    protected function checkSsoIdentityAwareUserClass(InputInterface $input, OutputInterface $output): bool
    {
        $userReflectionClass = $this->getUserClass($output);
        if (!$userReflectionClass instanceof \ReflectionClass) {
            return false;
        }

        $hasSsoAwareInterface = $userReflectionClass->implementsInterface(SsoAwareUserInterface::class);

        if ($hasSsoAwareInterface === true) {
            $output->writeln(sprintf('<info>√ "%s" extends from "%s"</info>', $userReflectionClass->getName(), SsoAwareUserInterface::class));

            return true;
        }

        $output->writeln(sprintf(
            '<error>x</error> <question>SsoAwareUserInterface missing.</question> "%s" needs to implement "%s". Add it in your class definition (Use "%s" instead of "%s").',
            $userReflectionClass->getShortName(),
            SsoAwareUserInterface::class,
            AbstractSsoAwareUser::class,
            AbstractUser::class
        ));

        return false;
    }

    protected function checkSsoIdentityRelationStep(InputInterface $input, OutputInterface $output): bool
    {
        $userReflectionClass = $this->getUserClass($output);
        if (!$userReflectionClass instanceof \ReflectionClass) {
            return false;
        }

        $hasRelation = $userReflectionClass->hasMethod('getSsoIdentities');

        if ($hasRelation === true) {
            $output->writeln(sprintf('<info>√ SsoIdentity relation is installed in class (%s)</info>', $userReflectionClass->getName()));

            return true;
        }

        $arg = '
{
    "fieldtype": "manyToManyObjectRelation",
    "width": "",
    "height": "",
    "maxItems": "",
    "queryColumnType": "text",
    "phpdocType": "array",
    "relationType": true,
    "visibleFields": "key",
    "optimizedAdminLoading": false,
    "visibleFieldDefinitions": [],
    "lazyLoading": true,
    "classes": [
        {
            "classes": "SsoIdentity"
        }
    ],
    "pathFormatterClass": "",
    "name": "ssoIdentities",
    "title": "SSO Identities",
    "tooltip": "",
    "mandatory": false,
    "noteditable": false,
    "index": false,
    "locked": false,
    "style": "",
    "permissions": null,
    "datatype": "data",
    "invisible": false,
    "visibleGridView": false,
    "visibleSearch": false
}';

        $output->writeln(sprintf(
            '<error>x</error> <question>SsoIdentity relation is missing.</question> Please add this field to your class definition, after "groups" (var/classes/definition_%s.php): %s. You need to re-save your class afterwards',
            $userReflectionClass->getShortName(),
            $arg
        ));

        return false;
    }

    protected function checkSsoIdentityClassStep(InputInterface $input, OutputInterface $output): bool
    {
        $list = new ClassDefinition\Listing();

        $identityClass = null;
        foreach ($list->load() as $classDefinition) {
            $class = sprintf('\\Pimcore\\Model\\DataObject\\%s', ucfirst($classDefinition->getName()));

            if (class_exists($class) === false) {
                continue;
            }

            if (in_array(SsoIdentityInterface::class, class_implements($class)) === false) {
                continue;
            }

            $identityClass = $class;
        }

        if ($identityClass !== null) {
            $output->writeln(sprintf('<info>√ SsoIdentityClass is installed (%s)</info>', $identityClass));

            return true;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<error>x</error> <question>SSoIdentityClass not found.</question> Do you want to install it now? (y/n) ', false);

        if ($input->isInteractive() === true && !$helper->ask($input, $output, $question)) {
            return false;
        }

        $this->classInstaller->setLogger($output);
        $this->classInstaller->installClasses(['SsoIdentity']);

        return true;
    }

    protected function checkInstalledStep(InputInterface $input, OutputInterface $output): bool
    {
        if ($this->oauthEnabled === true) {
            $output->writeln(sprintf('<info>√ OAuth is enabled</info>'));

            return true;
        }

        $arg = '
members:
    oauth:
        enabled: true';

        $output->writeln(sprintf('<error>x</error> <question>Oauth is disabled.</question> Please enable add in your config.yml: %s', $arg));

        return false;
    }

    protected function getUserClass(OutputInterface $output): ?\ReflectionClass
    {
        $userClass = $this->classManager->getUserClass();

        try {
            $reflectionClass = new \ReflectionClass($userClass);
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>x</error> Error while checking user class (%s). Error was: <error>%s</error>', $userClass, $e->getMessage()));

            return null;
        }

        return $reflectionClass;
    }
}
