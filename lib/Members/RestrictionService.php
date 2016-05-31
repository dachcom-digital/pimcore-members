<?php

namespace Members;

use Members\Model\Restriction;

class RestrictionService
{
    public static function deleteRestriction( $obj, $cType )
    {
        $docId =  $obj->getId();
        $restriction = FALSE;

        try
        {
            $restriction = Restriction::getByTargetId( $docId, $cType );
        }
        catch(\Exception $e)
        {
        }

        if( $restriction !== FALSE)
        {
            $restriction->delete();
        }

        return TRUE;

    }

    public static function checkRestriction( $obj, $cType)
    {
        $parentId = $obj->getParentId();

        if( $parentId == 0)
        {
            return TRUE;
        }

        //get all child elements and store them in members table!
        $type = 'document';
        if( $cType == 'object')
        {
            $type = 'object';
        }

        $parentObj = \Pimcore\Model\Element\Service::getElementById($type, $parentId);

        if( empty( $parentObj ) )
        {
            return TRUE;
        }

        $parentRestriction = NULL;

        try
        {
            $parentRestriction = Restriction::getByTargetId( $parentObj->getId(), $cType );
        }
        catch(\Exception $e)
        {
        }

        if( !$parentRestriction instanceof \Members\Model\Restriction)
        {
            return TRUE;
        }

        //parent has disabled child inherit
        if( !$parentRestriction->getInherit() && !$parentRestriction->isInherited() )
        {
            return TRUE;
        }

        $restriction = new Restriction();
        $restriction->setTargetId( $obj->getId() );

        $restriction->setCtype( $cType );
        $restriction->setIsInherited( TRUE );
        $restriction->setRelatedGroups( $parentRestriction->getRelatedGroups() );
        $restriction->save();

        return TRUE;
    }

}