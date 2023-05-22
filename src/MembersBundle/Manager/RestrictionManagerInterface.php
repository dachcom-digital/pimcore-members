<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\Element\ElementInterface;

interface RestrictionManagerInterface
{
    public function getElementRestrictedGroups(ElementInterface $element): array;

    public function getElementRestrictionStatus(ElementInterface $element): ElementRestriction;

    public function isFrontendRequestByAdmin(): bool;

    public function elementIsInProtectedStorageFolder(ElementInterface $element): bool;

    public function pathIsInProtectedStorageFolder(string $path): bool;

    public function getUser(): ?UserInterface;
}
