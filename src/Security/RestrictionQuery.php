<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Security;

use Doctrine\DBAL\Query\QueryBuilder;
use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Model;

class RestrictionQuery
{
    public function __construct(protected RestrictionManagerInterface $restrictionManager)
    {
    }

    public function addRestrictionInjection(QueryBuilder $query, Model\Listing\AbstractListing $listing, string $queryIdentifier = 'id', ?string $aliasFrom = null): void
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
            $additionalQuery = sprintf(' AND assets.path NOT LIKE "/%s%%"', RestrictionManager::PROTECTED_ASSET_FOLDER);
        } elseif ($listing instanceof Model\Document\Listing) {
            $cType = 'page';
            $typedAliasFrom = 'documents';
        } else {
            throw new \Exception(sprintf('Cannot determinate listing of class "%s"', get_class($listing)));
        }

        $aliasFrom = $aliasFrom ?? $typedAliasFrom;

        $query->leftJoin(
            $aliasFrom,
            'members_restrictions',
            'mr',
            sprintf('mr.targetId = %s.%s AND mr.ctype = "%s"', $aliasFrom, $queryIdentifier, $cType),
        );

        $query->leftJoin(
            $aliasFrom,
            'members_group_relations',
            'mgr',
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
