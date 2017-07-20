<?php

namespace MembersBundle\Restriction;

use Pimcore\Model;

class RestrictionService
{
    /**
     * Triggered by pre deletion events of all types.
     *
     * @param $obj
     * @param $cType
     *
     * @return bool
     */
    public function deleteRestriction($obj, $cType)
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
     * Triggered by post update events of all types ONLY when element gets moved in tree!
     * Check if element is in right context
     *
     * @param \Pimcore\Model\AbstractModel $obj
     * @param string                       $cType
     *
     * @return bool
     */
    public function checkRestrictionContext($obj, $cType)
    {
        $restriction = NULL;
        $parentRestriction = NULL;

        $hasRestriction = TRUE;
        $hasParentRestriction = FALSE;

        try {
            //get current restriction
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            //restriction has been removed.
            $hasRestriction = FALSE;
        }

        //get closest inherit object with restriction
        $closestInheritanceParent = self::findClosestInheritanceParent($obj->getId(), $cType, TRUE);
        if (!is_null($closestInheritanceParent['id'])) {
            $parentRestriction = $closestInheritanceParent['restriction'];
            $hasParentRestriction = TRUE;
        }

        if ($hasParentRestriction && !$hasRestriction) {

            $restriction = new Restriction();
            $restriction->setTargetId($obj->getId());
            $restriction->setCtype($cType);
            $restriction->setIsInherited(TRUE);
            $restriction->setRelatedGroups($parentRestriction->getRelatedGroups());
            $restriction->save();
        } else if (!$hasParentRestriction && $hasRestriction) {
            $restriction->setIsInherited(FALSE);
            $restriction->save();
        } else if ($hasParentRestriction && $hasRestriction) {
            //nothing to do so far.
        } else if (!$hasParentRestriction && !$hasRestriction) {
            //nothing to do so far.
        }

        self::updateChildren($obj, $cType);

        return TRUE;
    }

    /**
     * @param $obj
     * @param $cType
     *
     * @return bool
     */
    private function updateChildren($obj, $cType)
    {
        $mainNodeRestriction = NULL;
        $closestParent = NULL;

        try {
            $mainNodeRestriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            $closestParent = self::findClosestInheritanceParent($obj->getId(), $cType);

            if (!is_null($closestParent['id'])) {
                $mainNodeRestriction = $closestParent['restriction'];
            }
        }

        $list = NULL;

        if ($obj instanceof \Pimcore\Model\Object\AbstractObject) {
            $list = new \Pimcore\Model\Object\Listing();
            $list->setCondition("o_type = ? AND o_path LIKE ?", ['object', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(o_path) ASC', FALSE);
        } else if ($obj instanceof \Pimcore\Model\Document) {
            $list = new \Pimcore\Model\Document\Listing();
            $list->setCondition("type = ? AND path LIKE ?", ['page', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', FALSE);
        } else if ($obj->getType() === 'folder' && $obj instanceof \Pimcore\Model\Asset) {
            $list = new \Pimcore\Model\Asset\Listing();
            $list->setCondition("path LIKE ?", [$obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', FALSE);
        }

        if ($list === NULL) {
            return TRUE;
        }

        $excludePaths = [];
        $children = $list->load();

        if (!empty($children)) {

            /** @var \Pimcore\Model\AbstractModel $child */
            foreach ($children as $child) {

                $isNew = FALSE;
                $skip = FALSE;

                foreach ($excludePaths as $path) {
                    if (substr($child->getFullPath(), 0, strlen($path)) === $path) {
                        $skip = TRUE;
                        break;
                    }
                }

                if ($skip === TRUE) {
                    continue;
                }

                $targetType = $obj->getType();
                if ($cType === 'asset' && $targetType === 'folder') {
                    $targetType = 'asset';
                }

                try {
                    $childRestriction = Restriction::getByTargetId($child->getId(), $targetType);
                } catch (\Exception $e) {
                    $childRestriction = new Restriction();
                    $childRestriction->setTargetId($child->getId());
                    $childRestriction->setCtype($cType);
                    $isNew = TRUE;
                }

                if ($isNew == FALSE && $childRestriction->isInherited() === FALSE) {
                    $excludePaths[] = $child->getFullPath();
                    continue;
                }

                // 1. main node or next closest restriction node has been deleted.
                if (!$mainNodeRestriction instanceof Restriction) {
                    $childRestriction->delete();
                    // 2. main node exists. if it's inheritable, pass it through.
                } else if ($mainNodeRestriction->getInherit() === TRUE || $mainNodeRestriction->getIsInherited() === TRUE) {
                    $childRestriction->setIsInherited(TRUE);
                    $childRestriction->setRelatedGroups($mainNodeRestriction->getRelatedGroups());
                    $childRestriction->save();
                    // 3. main node exists and has no (longer) any inheritable functions. if child inherits, delete it.
                } else if ($mainNodeRestriction->getInherit() === FALSE && $childRestriction->getIsInherited() === TRUE) {
                    $childRestriction->delete();
                }
            }
        }

        return TRUE;
    }

    /**
     * @param      $elementId
     * @param      $cType
     * @param bool $forcePathDetection
     *
     * @return array
     */
    public function findClosestInheritanceParent($elementId, $cType, $forcePathDetection = FALSE)
    {
        $type = 'document';
        if ($cType == 'object') {
            $type = 'object';
        } else if ($cType == 'asset') {
            $type = 'asset';
        }

        $parentPath = NULL;
        $parentKey = NULL;
        $parentId = NULL;
        $restriction = NULL;

        $obj = Model\Element\Service::getElementById($type, $elementId);

        if ($obj instanceof Model\AbstractModel) {

            $currentRestriction = FALSE;
            try {
                $currentRestriction = Restriction::getByTargetId($obj->getId(), $cType);
            } catch (\Exception $e) {
            }

            if ($forcePathDetection === TRUE || ($currentRestriction instanceof Restriction && $currentRestriction->getIsInherited() === TRUE)) {

                $path = urldecode($obj->getRealFullPath());

                $paths = ['/'];
                $tmpPaths = [];
                $pathParts = explode('/', $path);
                foreach ($pathParts as $pathPart) {
                    $tmpPaths[] = $pathPart;
                    $t = implode('/', $tmpPaths);
                    if (!empty($t)) {
                        $paths[] = $t;
                    }
                }

                $paths = array_reverse($paths);

                $currentPath = array_shift($paths);

                if ($obj instanceof Model\Object\AbstractObject) {
                    $class = '\Pimcore\Model\Object\AbstractObject';
                } else if ($obj instanceof Model\Document) {
                    $class = '\Pimcore\Model\Document';
                } else if ($obj instanceof Model\Asset) {
                    $class = '\Pimcore\Model\Asset';
                }

                foreach ($paths as $p) {

                    /** @var \Pimcore\Model\AbstractModel $el */
                    if ($el = $class::getByPath($p)) {

                        $restriction = FALSE;
                        try {
                            $restriction = Restriction::getByTargetId($el->getId(), $cType);
                        } catch (\Exception $e) {
                        }

                        if ($restriction instanceof Restriction && ($restriction->getInherit() === TRUE || $restriction->getIsInherited() === TRUE)) {
                            $parentPath = $el->getFullPath();
                            $parentKey = $el->getKey();
                            $parentId = $el->getId();
                            break;
                        }
                    }
                }
            }
        }

        return ['path' => $parentPath, 'key' => $parentKey, 'id' => $parentId, 'restriction' => $restriction];
    }
}