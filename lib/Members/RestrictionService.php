<?php

namespace Members;

use Members\Model\Restriction;

class RestrictionService
{
    public static function deleteRestriction( $obj, $cType ) {

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

        return true;

    }

}