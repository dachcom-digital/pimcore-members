<?php

namespace DachcomBundle\Test\rest;

use DachcomBundle\Test\Helper\Rest\BrowserKitRestClient;
use DachcomBundle\Test\Util\MembersHelper;
use Pimcore\Tests\Test\RestTestCase;

abstract class AbstractRestTestCase extends RestTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->restClient = new BrowserKitRestClient($this->tester->getHttpClient());

        if ($this->authenticateUser) {
            $this->restClient->setApiKey($this->tester->getRestApiKey($this->authenticateUser));
        }
    }

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
        MembersHelper::cleanUp();
        MembersHelper::reCreateMembersStructure();

        parent::_after();
    }
}
