<?php

namespace DachcomBundle\Test\rest;

use Dachcom\Codeception\Util\SystemHelper;
use DachcomBundle\Test\Util\MembersHelper;
use Pimcore\Tests\Test\RestTestCase;

abstract class AbstractRestTestCase extends RestTestCase
{
    /**
     * Params which will be added to each request
     *
     * @return array
     */
    public function getGlobalRequestParams()
    {
        return [];
    }

    protected function _after()
    {
        SystemHelper::cleanUp(['members_restrictions', 'members_group_relations']);
        MembersHelper::reCreateMembersStructure();

        parent::_after();
    }
}
