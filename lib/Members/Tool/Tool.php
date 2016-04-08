<?php

namespace Members\Tool;

use Members\Auth\Adapter;
use Members\Model\Restriction;
use Members\Model\Configuration;

use Pimcore\Model\Object;

class Tool {

    public static function generateNavCacheKey()
    {
        $identity = self::getIdentity();

        if( $identity instanceof Object\Member)
        {
            $allowedGroups = $identity->getGroups();

            if( !empty( $allowedGroups ))
            {
                $m = implode('-', $allowedGroups );
                return md5( $m );
            }

            return TRUE;
        }

        return TRUE;

    }

    public static function getDocumentRestrictedGroups( $id )
    {
        $restriction = FALSE;

        try
        {
            $restriction = Restriction::getByTargetId( $id );
        }
        catch(\Exception $e)
        {
        }

        $groups = array();

        if( $restriction !== FALSE && is_array( $restriction->relatedGroups))
        {
            $groups = $restriction->relatedGroups;
        }
        else
        {
            $groups[] = 'default';
        }

        return $groups;
    }

    public static function isRestrictedDocument( \Pimcore\Model\Document\Page $document )
    {
        $restriction = FALSE;

        try
        {
            $restriction = Restriction::getByTargetId( $document->getId() );
        }
        catch(\Exception $e)
        {
        }

        if($restriction === FALSE)
        {
            $docParentIds = $document->getDao()->getParentIds();
            $nextHigherRestriction = Restriction::findNextInherited( $document->getId(), $docParentIds );

            if( $nextHigherRestriction->getId() !== null )
            {
                $restriction = $nextHigherRestriction;
            }
            else
            {
                return FALSE;
            }
        }

        $identity = self::getIdentity();

        $restrictionRelatedGroups = $restriction->getRelatedGroups();

        if( !empty( $restrictionRelatedGroups ) && $identity instanceof Object\Member)
        {
            $allowedGroups = $identity->getGroups();
            $intersectResult = array_intersect($restrictionRelatedGroups, $allowedGroups);

            if( count($intersectResult) > 0 )
            {
                return FALSE;
            }

        }

        return TRUE;
    }

    private static function getIdentity($forceFromStorage = false)
    {
        $identity = \Zend_Auth::getInstance()->getIdentity();

        if (!$identity && isset($_SERVER['PHP_AUTH_PW']))
        {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];

            $identity = self::getServerIdentity( $username, $password );
        }

        if ($identity && $forceFromStorage)
        {
            $identity = Object\Member::getById($identity->getId());
        }

        if( $identity instanceof \Pimcore\Model\Object\Member )
        {
            return $identity;
        }

        return FALSE;

    }

    private static function getServerIdentity( $username, $password )
    {
        $auth = \Zend_Auth::getInstance();

        $adapterSettings = array(

            'identityClassname' =>  Configuration::get('auth.adapter.identityClassname'),
            'identityColumn' =>  Configuration::get('auth.adapter.identityColumn'),
            'credentialColumn' =>  Configuration::get('auth.adapter.credentialColumn'),
            'objectPath' =>  Configuration::get('auth.adapter.objectPath')

        );

        $adapter = new Adapter( $adapterSettings );
        $adapter
            ->setIdentity($username)
            ->setCredential($password);
        $result = $auth->authenticate($adapter);

        if ($result->isValid())
        {
            return \Zend_Auth::getInstance()->getIdentity();
        }

        return FALSE;

    }
}
