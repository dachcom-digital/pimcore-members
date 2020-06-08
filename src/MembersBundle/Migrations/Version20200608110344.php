<?php

namespace MembersBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MembersBundle\Configuration\Configuration;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\DataObject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20200608110344 extends AbstractPimcoreMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return bool
     */
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     *
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $className = null;
        $membersDefinition = null;

        $configuration = $this->container->get(Configuration::class);
        $className = $configuration->getConfig('user');

        if (!empty($className['adapter']['class_name'])) {
            $className = ucfirst($className['adapter']['class_name']);
        }

        if (!empty($className)) {
            $membersDefinition = DataObject\ClassDefinition::getByName($className);
        }

        if (!$membersDefinition instanceof DataObject\ClassDefinition) {
            $this->write('<error>No valid Members User class found. Please update class definition manually in your class manager.</error>');
            return;
        }

        $this->writeMessage(sprintf('Saving php files for class: %s', $membersDefinition->getName()));
        $membersDefinition->generateClassFiles(false);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
