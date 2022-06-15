<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Model;
use Doctrine\DBAL\Query\QueryBuilder;

class RestrictionQuery
{
    protected RestrictionManagerInterface $restrictionManager;

    public function __construct(RestrictionManagerInterface $restrictionManager)
    {
        $this->restrictionManager = $restrictionManager;
    }

    public function addRestrictionInjection(QueryBuilder $query, Model\Listing\AbstractListing $listing, string $queryIdentifier = 'o_id', ?string $aliasFrom = null)
    {
        $additionalQuery = '';
        $allowedGroups = [];

        if ($this->restrictionManager->isFrontendRequestByAdmin()) {
            return;
        }

        if ($this->restrictionManager->getUser() instanceof UserInterface) {
            $groups = $this->restrictionManager->getUser()->getGroups();
            /** @var GroupInterface $group */
            foreach ($groups as $group) {
                $allowedGroups[] = $group->getId();
            }
        }

        if ($listing instanceof Model\DataObject\Listing) {
            $cType = 'object';
            $typedAliasFrom = $listing->getDao()->getTableName();
        } elseif ($listing instanceof Model\Asset\Listing) {
            $cType = 'asset';
            $typedAliasFrom = 'assets';
            $additionalQuery = sprintf(' AND assets.path NOT LIKE "/%s%%"', RestrictionUri::PROTECTED_ASSET_FOLDER);
        } elseif ($listing instanceof Model\Document\Listing) {
            $cType = 'page';
            $typedAliasFrom = 'documents';
        } else {
            throw new \Exception(sprintf('Cannot determinate listing of class "%s"', get_class($listing)));
        }

        $aliasFrom = $aliasFrom ?? $typedAliasFrom;

        $query->leftJoin($aliasFrom, 'members_restrictions', 'mr',
            sprintf('mr.targetId = %s.%s AND mr.ctype = "%s"', $aliasFrom, $queryIdentifier, $cType),
        );

        $query->leftJoin($aliasFrom, 'members_group_relations', 'mgr',
            'mgr.restrictionId = mr.id'
        );

        if (count($allowedGroups) > 0) {
            $subQuery = sprintf('(mr.targetId IS NULL%s)', $additionalQuery);
            $queryStr = sprintf('%s OR (mr.ctype = "%s" AND mgr.groupId IN (%s))', $subQuery, $cType, implode(',', $allowedGroups));
        } else {
            $queryStr = sprintf('mr.targetId IS NULL%s', $additionalQuery);
        }

        $query->andWhere($queryStr);
        $query->addGroupBy(sprintf('%s.%s', $aliasFrom, $queryIdentifier));
    }
}
