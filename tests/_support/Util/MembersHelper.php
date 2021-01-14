<?php

namespace DachcomBundle\Test\Util;

use MembersBundle\Tool\Install;

class MembersHelper
{
    const DEFAULT_FEU_USERNAME = 'chuck';
    const DEFAULT_FEU_EMAIL = 'test@universe.org';
    const DEFAULT_FEU_PASSWORD = 'default-password';
    const DEFAULT_FEG_NAME = 'Default Group';

    public static function reCreateMembersStructure()
    {
        $installer = \Pimcore::getContainer()->get(Install::class);
        $installer->initializeFreshSetup();
    }
}
