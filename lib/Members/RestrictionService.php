<?php

namespace Members;

use Members\Model\Restriction;
use Pimcore\Model\Asset;
use Pimcore\Model\Element;

class RestrictionService
{
    /**
     * @param $obj
     * @param $cType
     *
     * @return bool
     */
    public static function deleteRestriction($obj, $cType)
    {
        $docId = $obj->getId();
        $restriction = FALSE;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        if ($restriction !== FALSE) {
            $restriction->delete();
        }

        return TRUE;
    }

    /**
     * Resolve Restriction if Element gets moved out of a inherited Structure
     * @param $obj
     * @param $orgObj
     * @param $cType
     *
     * @return bool
     */
    public static function resolveRestriction($obj, $orgObj, $cType)
    {
        $front = \Zend_Controller_Front::getInstance();

        $parentId = NULL;

        if($cType === 'object') {
            $values = json_decode($front->getRequest()->getParam('values'));
            $parentId = $values->parentId;
        } else if( $cType === 'asset') {
            $parentId = $front->getRequest()->getParam('parentId');
        } else if( $cType === 'page') {
            $parentId = $front->getRequest()->getParam('parentId');
        }

        if(!is_numeric($parentId)) {
            return FALSE;
        }

        if((int)$parentId === (int)$orgObj->getParentId()) {
            return FALSE;
        }

        $docId = $obj->getId();
        $restriction = FALSE;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        if(!$restriction instanceof Restriction) {
            return FALSE;
        }

        if ($restriction->isInherited()) {
            $restriction->setIsInherited(FALSE);
            $restriction->save();
        }

        self::checkRestriction($obj, $cType);

        return TRUE;
    }

    /**
     * @param $obj
     * @param $cType
     *
     * @return bool
     */
    public static function checkRestriction($obj, $cType)
    {
        $parentId = $obj->getParentId();

        if ($parentId == 0) {
            return TRUE;
        }

        //get all child elements and store them in members table!
        $type = 'document';
        if ($cType === 'object') {
            $type = 'object';
        } else if ($cType === 'asset') {
            $type = 'asset';
        }

        $parentObj = Element\Service::getElementById($type, $parentId);

        if (empty($parentObj)) {
            return TRUE;
        }

        $parentRestriction = NULL;

        try {
            $parentRestriction = Restriction::getByTargetId($parentObj->getId(), $cType);
        } catch (\Exception $e) {
        }

        if (!$parentRestriction instanceof Restriction) {
            return TRUE;
        }

        //parent has disabled child inherit
        if (!$parentRestriction->getInherit() && !$parentRestriction->isInherited()) {
            return TRUE;
        }

        try {
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            $restriction = new Restriction();
            $restriction->setTargetId($obj->getId());
            $restriction->setCtype($cType);
        }

        $restriction->setIsInherited(TRUE);
        $restriction->setRelatedGroups($parentRestriction->getRelatedGroups());
        $restriction->save();

        return TRUE;
    }

}