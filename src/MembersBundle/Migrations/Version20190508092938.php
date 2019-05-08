<?php

namespace MembersBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use MembersBundle\Adapter\Group\AbstractGroup;
use Pimcore\Migrations\Migration\AbstractPimcoreMigration;
use Pimcore\Model\DataObject;

class Version20190508092938 extends AbstractPimcoreMigration
{
    /**
     * @return bool
     */
    public function doesSqlMigrations(): bool
    {
        return false;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $groupFound = false;

        $list = new DataObject\ClassDefinition\Listing();
        $list = $list->load();

        foreach ($list as $class) {
            if ($groupFound === true) {
                break;
            }

            if ($class->getParentClass() === sprintf('\%s', AbstractGroup::class)) {
                if (is_array($class->getFieldDefinitions())) {
                    foreach ($class->getFieldDefinitions() as $fieldDefinition) {
                        if ($fieldDefinition->getName() === 'roles') {
                            if ($fieldDefinition instanceof DataObject\ClassDefinition\Data\Multiselect) {
                                if ($fieldDefinition->getOptionsProviderClass() === '@MembersBundle\CoreExtension\Provider\RoleOptionsProvider') {
                                    // already a service. skip....
                                    $this->writeMessage('RoleOptionsProvider already defined as service. skipping...');
                                    $groupFound = true;

                                    break;
                                } elseif ($fieldDefinition->getOptionsProviderClass() === 'MembersBundle\CoreExtension\Provider\RoleOptionsProvider') {
                                    $groupFound = true;
                                    $fieldDefinition->setOptionsProviderClass('@MembersBundle\CoreExtension\Provider\RoleOptionsProvider');
                                    $this->writeMessage('Convert RoleOptionsProvider to symfony service...');
                                    $class->save();

                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($groupFound === false) {
            $this->write(
                sprintf(
                    '<error>No valid Members Group Class found. Please change "options provider class" of field "roles" to "%s" manually.</error>',
                    '@MembersBundle\CoreExtension\Provider\RoleOptionsProvider'
                )
            );
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
