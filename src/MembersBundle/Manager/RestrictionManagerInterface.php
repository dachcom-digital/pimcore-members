<?php

namespace MembersBundle\Manager;

use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\AbstractModel;

interface RestrictionManagerInterface
{
    /**
     * @param AbstractModel $element
     *
     * @return bool|array
     */
    public function getElementRestrictedGroups(AbstractModel $element);

    /**
     * @param AbstractModel $element
     *
     * @return ElementRestriction
     */
    public function getElementRestrictionStatus(AbstractModel $element);

    /**
     * @todo: bring it into pimcore context.
     *
     * @return bool
     */
    public function isFrontendRequestByAdmin();

    /**
     * @return UserInterface|null
     */
    public function getUser();
}
