<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Restriction\ElementRestriction;
use MembersBundle\Restriction\Restriction;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Tool;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RestrictionManager implements RestrictionManagerInterface
{
    public const PROTECTED_ASSET_FOLDER = 'restricted-assets';

    public const RESTRICTION_STATE_LOGGED_IN = 'members.restriction.logged_in';
    public const RESTRICTION_STATE_NOT_LOGGED_IN = 'members.restriction.not_logged_in';
    public const RESTRICTION_SECTION_ALLOWED = 'members.restriction.allowed';
    public const RESTRICTION_SECTION_NOT_ALLOWED = 'members.restriction.not_allowed';
    public const RESTRICTION_SECTION_REFUSED = 'members.restriction.refused';
    public const REQUEST_RESTRICTION_STORAGE = 'members.restriction.store';

    public function __construct(
        protected Configuration $configuration,
        protected TokenStorageInterface $tokenStorage
    ) {
    }

    public function getElementRestrictedGroups(ElementInterface $element): array
    {
        $restriction = false;
        $groups[] = 'default';

        if ($element instanceof Document) {
            $restriction = $this->getRestrictionElement($element, 'page');
        } elseif ($element instanceof DataObject\Concrete) {
            $restriction = $this->getRestrictionElement($element, 'object');
        } elseif ($element instanceof Asset) {
            $restriction = $this->getRestrictionElement($element, 'asset');
        }

        if (!$restriction instanceof Restriction) {
            return $groups;
        }

        $groups = [];
        if (is_array($restriction->getRelatedGroups())) {
            $groups = $restriction->getRelatedGroups();
        }

        return $groups;
    }

    public function getElementRestrictionStatus(ElementInterface $element): ElementRestriction
    {
        $user = $this->getUser();
        $elementRestriction = new ElementRestriction();

        $restriction = null;
        if ($element instanceof Document) {
            $restriction = $this->getRestrictionElement($element, 'page');
        } elseif ($element instanceof DataObject\Concrete) {
            $restriction = $this->getRestrictionElement($element, 'object');
        } elseif ($element instanceof Asset) {
            $restriction = $this->getRestrictionElement($element, 'asset');
        }

        if ($user instanceof UserInterface) {
            $elementRestriction->setState(self::RESTRICTION_STATE_LOGGED_IN);
        }

        if ($restriction === null) {
            if ($element instanceof Asset) {
                //protect asset if element is in restricted area with no added restriction group.
                $elementRestriction->setSection($this->isFrontendRequestByAdmin() || !$this->elementIsInProtectedStorageFolder($element)
                    ? self::RESTRICTION_SECTION_ALLOWED
                    : self::RESTRICTION_SECTION_NOT_ALLOWED
                );
            } else {
                $elementRestriction->setSection(self::RESTRICTION_SECTION_ALLOWED);
            }

            return $elementRestriction;
        }

        if (is_array($restriction->getRelatedGroups())) {
            $elementRestriction->setRestrictionGroups($restriction->getRelatedGroups());
        }

        //check if user is not logged in.
        if (!$user instanceof UserInterface) {
            return $elementRestriction;
        }

        return $elementRestriction->setSection($this->filterAllowedSectionToUser($user->getGroups(), $restriction->getRelatedGroups()));

    }

    private function filterAllowedSectionToUser(array $userGroups, array $elementGroups): string
    {
        $sectionStatus = self::RESTRICTION_SECTION_NOT_ALLOWED;

        if (!empty($elementGroups)) {
            $allowedGroups = [];

            /** @var GroupInterface $group */
            foreach ($userGroups as $group) {
                $allowedGroups[] = $group->getId();
            }

            $intersectResult = array_intersect($elementGroups, $allowedGroups);
            if (count($intersectResult) > 0) {
                $sectionStatus = self::RESTRICTION_SECTION_ALLOWED;
            }
        }

        return $sectionStatus;
    }

    private function getRestrictionElement(ElementInterface $element, string $cType = 'page'): ?Restriction
    {
        $restriction = null;

        if ($this->isFrontendRequestByAdmin()) {
            return null;
        }

        try {
            if ($cType === 'page') {
                $restriction = Restriction::getByTargetId($element->getId(), $cType);
            } elseif ($cType === 'asset') {
                $restriction = Restriction::getByTargetId($element->getId(), $cType);
            } else {
                $restrictionConfig = $this->configuration->getConfig('restriction');
                $allowedTypes = $restrictionConfig['allowed_objects'];
                if ($element instanceof DataObject\Concrete && in_array($element->getClass()->getName(), $allowedTypes, true)) {
                    $restriction = Restriction::getByTargetId($element->getId(), $cType);
                }
            }
        } catch (\Exception $e) {
            // fail silently
        }

        return $restriction;
    }

    public function elementIsInProtectedStorageFolder(ElementInterface $element): bool
    {
        if (!$element instanceof Asset) {
            return false;
        }

        return $this->pathIsInProtectedStorageFolder($element->getPath());
    }

    public function pathIsInProtectedStorageFolder(string $path): bool
    {
        return str_contains($path, self::PROTECTED_ASSET_FOLDER);
    }

    public function isFrontendRequestByAdmin(): bool
    {
        return Tool::isFrontendRequestByAdmin();
    }

    public function getUser(): ?UserInterface
    {
        $token = $this->tokenStorage->getToken();

        if (is_null($token)) {
            return null;
        }

        $user = $token->getUser();

        return $user instanceof UserInterface ? $user : null;
    }
}
