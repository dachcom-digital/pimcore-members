<?php

namespace DachcomBundle\Test\Helper;

use MembersBundle\Adapter\User\AbstractSsoAwareUser;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Tool\Console;

class PimcoreBundleCore extends \Dachcom\Codeception\Helper\PimcoreBundleCore
{
    /**
     * @param $settings
     *
     * @return string|void
     * @throws \Exception
     */
    protected function installBundle($settings)
    {
        parent::installBundle($settings);

        // install members classes
        $cmd = sprintf('%s %s/bin/console members:install:class -o --no-interaction --env=test', Console::getExecutable('php'), PIMCORE_PROJECT_ROOT);
        Console::exec($cmd);

        // change user parent class to AbstractSsoAwareUser
        $def = ClassDefinition::getByName('MembersUser');
        $def->setParentClass(AbstractSsoAwareUser::class);
        $def->save();
    }
}
