<?php

namespace MembersBundle\Command;

use Pimcore\Model\Object\ClassDefinition;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ClassInstallerCommand extends ContainerAwareCommand
{
    private $classes = [
        'MembersUser',
        'MembersGroup',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('members:install:class')
            ->setDescription('Install Members Default Classes')
            ->setHelp('This command will install a "MembersUser" and "MembersGroup" Class with all the required fields.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Do you want to install the classes now? (y/n) ', FALSE);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        foreach ($this->getClasses() as $className => $path) {

            $class = new ClassDefinition();
            $id = $class->getDao()->getIdByName($className);

            if ($id !== FALSE) {
                $output->writeln(sprintf('<comment>Class "%s" already exists.</comment>', $className));
                continue;
            }

            $class->setName($className);

            $data = file_get_contents($path);
            $success = ClassDefinition\Service::importClassDefinitionFromJson($class, $data);

            if (!$success) {
                $output->writeln(sprintf('Could not import Class "%s".', $className));
            } else {
                $output->writeln(sprintf('Class "%s" <info>successfully</info> installed.', $className));
            }
        }
    }

    /**
     * @return array
     */
    private function getClasses(): array
    {
        $result = [];

        foreach ($this->classes as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = realpath(dirname(__FILE__) . '/../Resources/install/classes') . '/' . $filename;
            $path = realpath($path);

            if (FALSE === $path || !is_file($path)) {
                throw new \RuntimeException(sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className, $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

}
