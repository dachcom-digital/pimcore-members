<?php

namespace MembersBundle\Event;

use MembersBundle\Restriction\Restriction;
use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\EventDispatcher\Event;

class RestrictionEvent extends Event
{
    /**
     * @var ElementInterface
     */
    protected $element;

    /**
     * @var Restriction|null
     */
    protected $restriction;

    /**
     * @param ElementInterface $element
     * @param Restriction|null $restriction
     */
    public function __construct(ElementInterface $element, ?Restriction $restriction)
    {
        $this->element = $element;
        $this->restriction = $restriction;
    }

    /**
     * @return ElementInterface
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return Restriction|null
     */
    public function getRestriction()
    {
        return $this->restriction;
    }
}
