<?php

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
