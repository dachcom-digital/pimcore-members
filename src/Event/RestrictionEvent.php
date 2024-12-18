<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Event;

use MembersBundle\Restriction\Restriction;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Contracts\EventDispatcher\Event;

class RestrictionEvent extends Event
{
    public function __construct(
        protected ElementInterface $element,
        protected ?Restriction $restriction
    ) {
    }

    public function getElement(): ElementInterface
    {
        return $this->element;
    }

    public function getRestriction(): ?Restriction
    {
        return $this->restriction;
    }
}
