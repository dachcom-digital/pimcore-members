<?php

namespace DachcomBundle\Test\Helper;

use Codeception\TestInterface;
use Dachcom\Codeception\Util\FileGeneratorHelper;
use Dachcom\Codeception\Util\SystemHelper;
use DachcomBundle\Test\Util\MembersHelper;

class PimcoreBackend extends \Dachcom\Codeception\Helper\PimcoreBackend
{
    /**
     * @param TestInterface $test
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function _after(TestInterface $test)
    {
        SystemHelper::cleanUp(['members_restrictions', 'members_group_relations']);
        FileGeneratorHelper::cleanUp();
        MembersHelper::reCreateMembersStructure();
    }

}
