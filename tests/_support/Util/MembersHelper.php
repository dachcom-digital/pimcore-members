<?php

namespace DachcomBundle\Test\Util;

use MembersBundle\Tool\Install;

class MembersHelper
{
    public const DEFAULT_FEU_USERNAME = 'chuck';
    public const DEFAULT_FEU_EMAIL = 'test@universe.org';
    public const DEFAULT_FEU_PASSWORD = 'default-password';
    public const DEFAULT_FEG_NAME = 'Default Group';

    public static function reCreateMembersStructure()
    {
        $installer = \Pimcore::getContainer()->get(Install::class);
        $installer->install();
    }
}
