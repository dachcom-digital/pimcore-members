<?php

namespace MembersBundle\Tool;

use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class ClassInstaller
{
    /**
     * @var OutputInterface
     */
    protected $logger;

    /**
     * @param OutputInterface $logger
     */
    public function setLogger(OutputInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $classes
     */
    public function installClasses(array $classes)
    {
        foreach ($this->getClasses($classes) as $className => $path) {
            $class = new ClassDefinition();
            $id = $class->getDao()->getIdByName($className);

            if ($id !== false) {
                $this->log(sprintf('<comment>Class "%s" already exists.</comment>', $className));

                continue;
            }

            $class->setName($className);

            $data = file_get_contents($path);
            $success = ClassDefinition\Service::importClassDefinitionFromJson($class, $data);

            if (!$success) {
                $this->log(sprintf('Could not import Class "%s".', $className));
            } else {
                $this->log(sprintf('Class "%s" <info>successfully</info> installed.', $className));
            }
        }
    }

    /**
     * @param array $classes
     *
     * @return array
     */
    protected function getClasses(array $classes): array
    {
        $result = [];

        foreach ($classes as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = realpath(dirname(__FILE__) . '/../Resources/install/classes') . '/' . $filename;
            $path = realpath($path);

            if (false === $path || !is_file($path)) {
                throw new \RuntimeException(sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className,
                    $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

    /**
     * @param string $msg
     */
    protected function log($msg)
    {
        if (!$this->logger instanceof OutputInterface) {
            return;
        }

        $this->logger->writeln($msg);
    }
}
