<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Restriction\ElementRestriction;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\DataObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RestrictionManager
{
    const RESTRICTION_STATE_LOGGED_IN = 'members.restriction.logged_in';

    const RESTRICTION_STATE_NOT_LOGGED_IN = 'members.restriction.not_logged_in';

    const RESTRICTION_SECTION_ALLOWED = 'members.restriction.allowed';

    const RESTRICTION_SECTION_NOT_ALLOWED = 'members.restriction.not_allowed';

    const RESTRICTION_SECTION_REFUSED = 'members.restriction.refused';

    const REQUEST_RESTRICTION_STORAGE = 'members.restriction.store';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * RestrictionManager constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param Configuration         $configuration
     */
    public function __construct(Configuration $configuration, TokenStorageInterface $tokenStorage)
    {
        $this->configuration = $configuration;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param AbstractModel $element
     *
     * @return bool|array
     */
    public function getElementRestrictedGroups(AbstractModel $element)
    {
        $restriction = FALSE;
        $groups[] = 'default';

        if ($element instanceof Document) {
            $restriction = $this->getRestrictionElement($element, 'page');
        } else if ($element instanceof DataObject) {
            $restriction = $this->getRestrictionElement($element, 'object');
        } else if ($element instanceof Asset) {
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

    /**
     * @param AbstractModel $element
     *
     * @return ElementRestriction
     */
    public function getElementRestrictionStatus(AbstractModel $element)
    {
        $user = $this->getUser();
        $elementRestriction = new ElementRestriction();

        $restriction = FALSE;
        if ($element instanceof Document) {
            $restriction = $this->getRestrictionElement($element, 'page');
        } else if ($element instanceof DataObject) {
            $restriction = $this->getRestrictionElement($element, 'object');
        } else if ($element instanceof Asset) {
            $restriction = $this->getRestrictionElement($element, 'asset');
        }

        if ($user instanceof UserInterface) {
            $elementRestriction->setState(self::RESTRICTION_STATE_LOGGED_IN);
        }

        if ($restriction === FALSE) {
            if ($element instanceof Asset) {
                //protect asset if element is in restricted area with no added restriction group.
                $elementRestriction->setSection($this->isFrontendRequestByAdmin() || strpos($element->getPath(), RestrictionUri::PROTECTED_ASSET_FOLDER) === FALSE
                    ? self::RESTRICTION_SECTION_ALLOWED
                    : self::RESTRICTION_SECTION_NOT_ALLOWED);
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
        } else {
            $elementRestriction->setSection($this->filterAllowedSectionToUser($user->getGroups(), $restriction->getRelatedGroups()));
            return $elementRestriction;
        }
    }

    /**
     * @param $userGroups
     * @param $elementGroups
     *
     * @return string
     */
    private function filterAllowedSectionToUser($userGroups, $elementGroups)
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

    /**
     * @param        $element (document|object)
     * @param string $cType
     *
     * @return bool|Restriction
     */
    private function getRestrictionElement($element, $cType = 'page')
    {
        $restriction = FALSE;

        if ($this->isFrontendRequestByAdmin()) {
            return FALSE;
        }

        try {
            if ($cType === 'page') {
                $restriction = Restriction::getByTargetId($element->getId(), $cType);
            } else if ($cType === 'asset') {
                $restriction = Restriction::getByTargetId($element->getId(), $cType);
            } else {
                $restrictionConfig = $this->configuration->getConfig('restriction');
                $allowedTypes = $restrictionConfig['allowed_objects'];
                if ($element instanceof DataObject\AbstractObject && in_array($element->getClass()->getName(), $allowedTypes)) {
                    $restriction = Restriction::getByTargetId($element->getId(), $cType);
                }
            }
        } catch (\Exception $e) {
        }

        return $restriction;
    }

    /**
     * @todo: bring it into pimcore context.
     * @return bool
     */
    public function isFrontendRequestByAdmin()
    {
        return \Pimcore\Tool::isFrontendRequestByAdmin();
    }

    /**
     * @return UserInterface|null
     */
    public function getUser()
    {
        $token = $this->tokenStorage->getToken();

        if (is_null($token)) {
            return NULL;
        }

        $user = $token->getUser();

        return $user instanceof UserInterface ? $user : NULL;
    }
}