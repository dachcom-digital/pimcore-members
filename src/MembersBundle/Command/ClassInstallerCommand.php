<?php

namespace MembersBundle\Command;

use MembersBundle\Tool\ClassInstaller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClassInstallerCommand extends Command
{
    protected static $defaultName = 'members:install:class';
    protected static $defaultDescription = 'Install Members Default Classes';

    protected ClassInstaller $classInstaller;

    public function setClassInstaller(ClassInstaller $classInstaller): void
    {
        $this->classInstaller = $classInstaller;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setHelp('This command will install a "MembersUser" and "MembersGroup" Class with all the required fields.')
            ->addOption(
                'oauth',
                'o',
                InputOption::VALUE_NONE,
                'Install Optional SSO Identity Class (Required if you are using the oauth2 feature)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = ['MembersUser', 'MembersGroup'];

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to install the classes now? (y/n) ', false);

        if ($input->isInteractive() === true && !$helper->ask($input, $output, $question)) {
            return 0;
        }

        $oauthInstall = $input->getOption('oauth') === true;

        if ($oauthInstall === true) {
            $classes = array_merge($classes, ['SsoIdentity']);
        }

        $this->classInstaller->setLogger($output);
        $this->classInstaller->installClasses($classes);

        return 0;
    }
}
