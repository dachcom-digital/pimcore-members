<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\Listing;

class RestrictionQuery
{
    /**
     * @var RestrictionManagerInterface
     */
    protected $restrictionManager;

    /**
     * RestrictionUri constructor.
     *
     * @param RestrictionManagerInterface $restrictionManager
     */
    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * @param QueryBuilder                           $query
     * @param \Pimcore\Model\Listing\AbstractListing $listing
     * @param string                                 $queryIdentifier
     */
    public function addRestrictionInjection(QueryBuilder $query, Listing\AbstractListing $listing, $queryIdentifier = 'o_id')
    {
        //always show data in backend.
        if ($this->restrictionManager->isFrontendRequestByAdmin()) {
            return;
        }

        $allowedGroups = [];
        if ($this->restrictionManager->getUser() instanceof UserInterface) {
            $groups = $this->restrictionManager->getUser()->getGroups();
            /** @var GroupInterface $group */
            foreach ($groups as $group) {
                $allowedGroups[] = $group->getId();
            }
        }

        $cType = 'object';
        if ($listing instanceof \Pimcore\Model\Asset\Listing) {
            $cType = 'asset';
        } elseif ($listing instanceof \Pimcore\Model\Document\Listing) {
            $cType = 'page';
        }

        $query->joinLeft(['members_restrictions' => 'members_restrictions'], 'members_restrictions.targetId = ' . $queryIdentifier . ' AND members_restrictions.ctype = "' . $cType . '"', '');
        $query->joinLeft(['members_group_relations' => 'members_group_relations'], 'members_group_relations.restrictionId = members_restrictions.id', '');

        $orQuery = '';
        if (count($allowedGroups) > 0) {
            $orQuery = 'OR (members_restrictions.ctype = "' . $cType . '" AND members_group_relations.groupId IN (' . implode(',', $allowedGroups) . ') )';
        }

        $query->where('members_restrictions.targetId IS NULL ' . $orQuery);
        $query->group($queryIdentifier);

    }
}