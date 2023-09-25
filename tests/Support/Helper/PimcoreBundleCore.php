<?php

namespace DachcomBundle\Test\Support\Helper;

use DachcomBundle\Test\Support\Util\MembersHelper;
use MembersBundle\Adapter\User\AbstractSsoAwareUser;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Tool\Console;

class PimcoreBundleCore extends \Dachcom\Codeception\Support\Helper\PimcoreBundleCore
{
    protected function installBundle(): void
    {
        parent::installBundle();

        // install members classes
        $cmd = sprintf('%s %s/bin/console members:install:class -o --no-interaction --env=test', Console::getExecutable('php'), PIMCORE_PROJECT_ROOT);
        shell_exec($cmd);

        // change user parent class to AbstractSsoAwareUser
        $def = ClassDefinition::getByName('MembersUser');
        $def->setParentClass(AbstractSsoAwareUser::class);
        $def->save();

        // we need to set a valid sender
        MembersHelper::assertMailSender();
    }
}
