<?php

namespace MembersBundle\Tool;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\Exception\NotFoundException;
use Symfony\Component\Console\Output\OutputInterface;

class ClassInstaller
{
    protected ?OutputInterface $logger = null;

    public function setLogger(OutputInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function installClasses(array $classes): void
    {
        foreach ($this->getClasses($classes) as $className => $path) {
            $class = new ClassDefinition();

            $id = null;

            try {
                $id = $class->getDao()->getIdByName($className);
            } catch(NotFoundException $e) {
                // fail silently
            }

            if ($id !== null) {
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

    protected function getClasses(array $classes): array
    {
        $result = [];

        // @todo: use flysystem configuration

        foreach ($classes as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = dirname(__DIR__) . '/Resources/install/classes' . '/' . $filename;
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

    protected function log(string $msg): void
    {
        if (!$this->logger instanceof OutputInterface) {
            return;
        }

        $this->logger->writeln($msg);
    }
}
