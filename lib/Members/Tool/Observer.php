<?php

namespace Members\Tool;

use Members\Auth;
use Members\Model\Restriction;
use Members\Model\Configuration;

use Pimcore\Model\Object;

class Observer {

    const STATE_LOGGED_IN = 'loggedIn';
    const STATE_NOT_LOGGED_IN = 'notLoggedIn';

    const SECTION_ALLOWED = 'allowed';
    const SECTION_NOT_ALLOWED = 'notAllowed';

    public static function generateNavCacheKey()
    {
        if ( self::isAdmin() )
        {
            return md5('pimcore_admin');
        }

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

    public static function getDocumentRestrictedGroups( $document )
    {
        $restriction = self::getRestrictionObject( $document, 'page', true );

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

    public static function getObjectRestrictedGroups( $document )
    {
        $restriction = self::getRestrictionObject( $document, 'object', true );

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

    /**
     * @param \Pimcore\Model\Document $document
     *
     * @return array (state, section)
     */
    public static function isRestrictedDocument( \Pimcore\Model\Document $document )
    {
        $cacheKey = self::generateIdentityDocumentCacheId($document);

        if( !$status = \Pimcore\Cache::load($cacheKey) )
        {
            $status = array('state' => self::STATE_NOT_LOGGED_IN, 'section' => self::SECTION_NOT_ALLOWED);
            $restriction = self::getRestrictionObject( $document, 'page' );
            $identity = self::getIdentity();

            if( $identity instanceof Object\Member )
            {
                $status['state'] = self::STATE_LOGGED_IN;
            }

            if( $restriction === FALSE)
            {
                $status['section'] = self::SECTION_ALLOWED;
                return $status;
            }

            $restrictionRelatedGroups = $restriction->getRelatedGroups();

            $identity = self::getIdentity();

            if( $identity instanceof Object\Member )
            {
                if( !empty( $restrictionRelatedGroups ) && $identity instanceof Object\Member)
                {
                    $allowedGroups = $identity->getGroups();

                    if( is_null($allowedGroups))
                    {
                        $allowedGroups = array();
                    }

                    $intersectResult = array_intersect($restrictionRelatedGroups, $allowedGroups);

                    if( count($intersectResult) > 0 )
                    {
                        $status['section'] = self::SECTION_ALLOWED;
                    }

                }

            }

            //store in cache.
            \Pimcore\Cache::save( $status, $cacheKey, ['members'], 999);

        }

        return $status;

    }


    /**
     * @param \Pimcore\Model\AbstractModel $object
     *
     * @return array (state, section)
     */
    public static function isRestrictedObject( \Pimcore\Model\AbstractModel $object )
    {
        $status = array('state' => self::STATE_NOT_LOGGED_IN, 'section' => self::SECTION_NOT_ALLOWED);
        $restriction = self::getRestrictionObject( $object, 'object' );
        $identity = self::getIdentity();

        if( $identity instanceof Object\Member )
        {
            $status['state'] = self::STATE_LOGGED_IN;
        }

        if( $restriction === FALSE)
        {
            $status['section'] = self::SECTION_ALLOWED;
            return $status;
        }

        $restrictionRelatedGroups = $restriction->getRelatedGroups();

        if( $identity instanceof Object\Member )
        {
            if( !empty( $restrictionRelatedGroups ) && $identity instanceof Object\Member)
            {
                $allowedGroups = $identity->getGroups();

                if( is_null($allowedGroups))
                {
                    $allowedGroups = array();
                }

                $intersectResult = array_intersect($restrictionRelatedGroups, $allowedGroups);

                if( count($intersectResult) > 0 )
                {
                    $status['section'] = self::SECTION_ALLOWED;
                }

            }

        }

        return $status;

    }

    public static function bindRestrictionToNavigation($document, $page)
    {
        $restrictedType = self::isRestrictedDocument($document);

        if( $restrictedType['section'] !== self::SECTION_ALLOWED )
        {
            $page->setActive(false);
            $page->setVisible(false);
        }

        return $page;

    }

    /**
     * Use this method for luceneSearch
     * @return array
     */
    public static function getCurrentUserAllowedGroups()
    {
        $ident = Auth\Instance::getAuth();
        $identity = $ident->getIdentity();

        if( $identity instanceof \Pimcore\Model\Object\Member )
        {
            $allowedGroups = $identity->getGroups();
            return $allowedGroups;
        }
    }

    /**
     * @param        $object (document|object)
     * @param string $cType
     * @param bool   $ignoreLoggedIn
     *
     * @return bool|\Members\Model\Restriction
     */
    private static function getRestrictionObject( $object, $cType = 'page', $ignoreLoggedIn = FALSE )
    {
        $restriction = FALSE;

        if ($ignoreLoggedIn == FALSE && self::isAdmin() )
        {
            //return FALSE;
        }

        try
        {
            if( $cType === 'page')
            {
                $restriction = Restriction::getByTargetId( $object->getId(), $cType );
            }
            else
            {
                $allowedTypes = Configuration::get('core.settings.object.allowed');
                if( $object instanceof \Pimcore\Model\Object\AbstractObject && in_array( $object->getClass()->getName(), $allowedTypes) )
                {
                    $restriction = Restriction::getByTargetId( $object->getId(), $cType );
                }
            }

        }
        catch(\Exception $e)
        {
        }

        return $restriction;

    }

    private static function getIdentity($forceFromStorage = false)
    {
        $ident = Auth\Instance::getAuth();
        $identity = $ident->getIdentity();

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
        $identifier = new Identifier();

        if ($identifier->setIdentity($username, $password)->isValid())
        {
            $ident = Auth\Instance::getAuth();

            return $ident->getIdentity();
        }

        return FALSE;
    }

    public static function isAdmin()
    {
        $u = \Pimcore\Tool\Authentication::authenticateSession();
        return $u instanceof \Pimcore\Model\User;
    }

    /**
     * Generate a Document Cache Key. It binds the logged in user id, if available.
     * @fixme: Is there a better solution: because of this, every document will be stored for each user. maybe we should store the doc id serialized to each user?
     * @param $document
     *
     * @return string
     */
    private static function generateIdentityDocumentCacheId($document)
    {
        $identity = self::getIdentity();
        $pageId =  $document->getId() . '_page_';

        $identityKey = 'noMember';

        if( $identity instanceof \Pimcore\Model\Object\Member )
        {
            $identityKey = $identity->getId();
        }

        return 'members_' . md5( $pageId . $identityKey );

    }
}