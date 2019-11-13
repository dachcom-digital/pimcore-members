<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\Listing\AbstractListing;

class RestrictionQuery
{
    /**
     * @var RestrictionManagerInterface
     */
    protected $restrictionManager;

    /**
     * @param RestrictionManagerInterface $restrictionManager
     */
    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    /**
     * @param QueryBuilder    $query
     * @param AbstractListing $listing
     * @param string          $queryIdentifier
     */
    public function addRestrictionInjection(QueryBuilder $query, AbstractListing $listing, $queryIdentifier = 'o_id')
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

        $query->joinLeft(
            ['members_restrictions' => 'members_restrictions'],
            sprintf('members_restrictions.targetId = %s AND members_restrictions.ctype = "%s"', $queryIdentifier, $cType),
            ''
        );

        $query->joinLeft(
            ['members_group_relations' => 'members_group_relations'],
            'members_group_relations.restrictionId = members_restrictions.id',
            ''
        );

        $assetQuery = '';
        if ($listing instanceof \Pimcore\Model\Asset\Listing) {
            $assetQuery = sprintf('assets.path NOT LIKE "/%s%%"', RestrictionUri::PROTECTED_ASSET_FOLDER);
        }

        if (count($allowedGroups) > 0) {
            $subQuery = sprintf('(members_restrictions.targetId IS NULL AND %s)', $assetQuery);
            $queryStr = sprintf('%s OR (members_restrictions.ctype = "%s" AND members_group_relations.groupId IN (%s))', $subQuery, $cType, implode(',', $allowedGroups));
        } else {
            $queryStr = $assetQuery;
        }

        $query->where($queryStr);
        $query->group($queryIdentifier);
    }
}
