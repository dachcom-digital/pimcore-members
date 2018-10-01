<?php

namespace Members\Tool;

use Members\Auth;
use Pimcore\Tool;
use Pimcore\Model\Object\Member;
use Pimcore\Model\Listing;

class Query
{
    /**
     * @param \Zend_Db_Select $query
     * @param \Pimcore\Model\Listing\AbstractListing $listing
     * @param string $queryIdentifier
     */
    public static function addRestrictionInjection( \Zend_Db_Select $query, Listing\AbstractListing $listing, $queryIdentifier = 'o_id')
    {
        $auth = Auth\Instance::getAuth();
        $identity = $auth->getIdentity();

        //always show data in backend.
        if(!Tool::isFrontentRequestByAdmin()) {

            $allowedGroups = [];
            if ($identity instanceof Member) {
                $allowedGroups = $identity->getGroups();
            }

            $cType = 'object';
            if( $listing instanceof \Pimcore\Model\Asset\Listing) {
                $cType = 'asset';
            } else if( $listing instanceof \Pimcore\Model\Document\Listing) {
                $cType = 'page';
            }

            $query->joinLeft(['members_restrictions' => 'members_restrictions'], 'members_restrictions.targetId = ' . $queryIdentifier . ' AND members_restrictions.ctype = "' . $cType . '"', '');
            $query->joinLeft(['members_group_relations' => 'members_group_relations'], 'members_group_relations.restrictionId = members_restrictions.id', '');

            $orQuery = '';
            if(count($allowedGroups) > 0) {
                $orQuery = 'OR (members_restrictions.ctype = "' . $cType . '" AND members_group_relations.groupId IN (' . implode(',', $allowedGroups) . ') )';
            }

            $query->where('members_restrictions.targetId IS NULL ' . $orQuery);

            if ($cType === 'asset') {
                $query->group('id');
            }
        }
    }
}