<?php

namespace DachcomBundle\Test\Support\Helper;

use Codeception\TestInterface;
use Dachcom\Codeception\Support\Util\FileGeneratorHelper;
use Dachcom\Codeception\Support\Util\SystemHelper;
use DachcomBundle\Test\Support\Util\MembersHelper;

class PimcoreBackend extends \Dachcom\Codeception\Support\Helper\PimcoreBackend
{
    public function _after(TestInterface $test): void
    {
        SystemHelper::cleanUp(['members_restrictions', 'members_group_relations']);
        FileGeneratorHelper::cleanUp();
        MembersHelper::reCreateMembersStructure();
        MembersHelper::assertMailSender();
    }
}
