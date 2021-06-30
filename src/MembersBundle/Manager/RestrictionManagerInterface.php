<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\AbstractModel;

interface RestrictionManagerInterface
{
    public function getElementRestrictedGroups(AbstractModel $element): ?array;

    public function getElementRestrictionStatus(AbstractModel $element): ElementRestriction;

    public function isFrontendRequestByAdmin(): bool;

    public function getUser(): ?UserInterface;
}
