<?php

namespace DachcomBundle\Test\Helper;

use Pimcore\Tests\Helper\PimcoreRest as PimcoreCoreRest;

class PimcoreRest extends PimcoreCoreRest
{
    /**
     * @inheritdoc
     */
    public function _beforeSuite($settings = [])
    {
        parent::_beforeSuite($settings);
    }
}
