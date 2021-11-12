<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Model\Listing\AbstractListing;
use Doctrine\DBAL\Query\QueryBuilder;

class RestrictionQuery
{
    protected RestrictionManagerInterface $restrictionManager;

    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    public function addRestrictionInjection(QueryBuilder $query, AbstractListing $listing, string $queryIdentifier = 'o_id')
    {
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

        // @todo: fix query

        $query->join(
            ['members_restrictions' => 'members_restrictions'],
            sprintf('members_restrictions.targetId = %s AND members_restrictions.ctype = "%s"', $queryIdentifier, $cType),
            ''
        );

        $query->join(
            ['members_group_relations' => 'members_group_relations'],
            'members_group_relations.restrictionId = members_restrictions.id',
            ''
        );

        $assetQuery = '';
        if ($listing instanceof \Pimcore\Model\Asset\Listing) {
            $assetQuery = sprintf('AND assets.path NOT LIKE "/%s%%"', RestrictionUri::PROTECTED_ASSET_FOLDER);
        }

        if (count($allowedGroups) > 0) {
            $subQuery = sprintf('(members_restrictions.targetId IS NULL %s)', $assetQuery);
            $queryStr = sprintf('%s OR (members_restrictions.ctype = "%s" AND members_group_relations.groupId IN (%s))', $subQuery, $cType, implode(',', $allowedGroups));
        } else {
            $queryStr = sprintf('members_restrictions.targetId IS NULL %s', $assetQuery);
        }

        $query->where($queryStr);
        $query->group($queryIdentifier);
    }
}
