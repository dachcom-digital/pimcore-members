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
     * @return bool
     */
    public function deleteRestriction($obj, $cType)
    {
        $docId = $obj->getId();
        $restriction = false;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        if ($restriction !== false) {
            $restriction->delete();
        }

        return true;
    }

    /**
     * Triggered by post update events of all types ONLY when element gets moved in tree!
     * Check if element is in right context
     *
     * @param \Pimcore\Model\AbstractModel $obj
     * @param string                       $cType
     * @return bool
     */
    public function checkRestrictionContext($obj, $cType)
    {
        $restriction = null;
        $parentRestriction = null;

        $hasRestriction = true;
        $hasParentRestriction = false;

        try {
            //get current restriction
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            //restriction has been removed.
            $hasRestriction = false;
        }

        //get closest inherit object with restriction
        $closestInheritanceParent = self::findClosestInheritanceParent($obj->getId(), $cType, true);
        if (!is_null($closestInheritanceParent['id'])) {
            $parentRestriction = $closestInheritanceParent['restriction'];
            $hasParentRestriction = true;
        }

        if ($hasParentRestriction && !$hasRestriction) {
            $restriction = new Restriction();
            $restriction->setTargetId($obj->getId());
            $restriction->setCtype($cType);
            $restriction->setIsInherited(true);
            $restriction->setRelatedGroups($parentRestriction->getRelatedGroups());
            $restriction->save();
        } elseif (!$hasParentRestriction && $hasRestriction) {
            $restriction->setIsInherited(false);
            $restriction->save();
        } elseif ($hasParentRestriction && $hasRestriction) {
            //nothing to do so far.
        } elseif (!$hasParentRestriction && !$hasRestriction) {
            //nothing to do so far.
        }

        self::updateChildren($obj, $cType);

        return true;
    }

    /**
     * @param $obj
     * @param $cType
     * @return bool
     */
    private function updateChildren($obj, $cType)
    {
        $mainNodeRestriction = null;
        $closestParent = null;

        try {
            $mainNodeRestriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            $closestParent = self::findClosestInheritanceParent($obj->getId(), $cType);

            if (!is_null($closestParent['id'])) {
                $mainNodeRestriction = $closestParent['restriction'];
            }
        }

        $list = null;

        if ($obj instanceof Model\DataObject\AbstractObject) {
            $list = new Model\DataObject\Listing();
            $list->setCondition("o_type = ? AND o_path LIKE ?", ['object', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(o_path) ASC', false);
        } elseif ($obj instanceof Model\Document) {
            $list = new Model\Document\Listing();
            $pathType = $obj instanceof Model\Document\Link ? 'link' : 'page';
            $list->setCondition("type = ? AND path LIKE ?", [$pathType, $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        } elseif ($obj->getType() === 'folder' && $obj instanceof Model\Asset) {
            $list = new Model\Asset\Listing();
            $list->setCondition("path LIKE ?", [$obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        }

        if ($list === null) {
            return true;
        }

        $excludePaths = [];
        $children = $list->load();

        if (!empty($children)) {

            /** @var \Pimcore\Model\AbstractModel $child */
            foreach ($children as $child) {

                $isNew = false;
                $skip = false;

                foreach ($excludePaths as $path) {
                    if (substr($child->getFullPath(), 0, strlen($path)) === $path) {
                        $skip = true;
                        break;
                    }
                }

                if ($skip === true) {
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
                    $isNew = true;
                }

                if ($isNew == false && $childRestriction->isInherited() === false) {
                    $excludePaths[] = $child->getFullPath();
                    continue;
                }

                // 1. main node or next closest restriction node has been deleted.
                if (!$mainNodeRestriction instanceof Restriction) {
                    $childRestriction->delete();
                    // 2. main node exists. if it's inheritable, pass it through.
                } elseif ($mainNodeRestriction->getInherit() === true || $mainNodeRestriction->getIsInherited() === true) {
                    $childRestriction->setIsInherited(true);
                    $childRestriction->setRelatedGroups($mainNodeRestriction->getRelatedGroups());
                    $childRestriction->save();
                    // 3. main node exists and has no (longer) any inheritable functions. if child inherits, delete it.
                } elseif ($mainNodeRestriction->getInherit() === false && $childRestriction->getIsInherited() === true) {
                    $childRestriction->delete();
                }
            }
        }

        return true;
    }

    /**
     * @param      $elementId
     * @param      $cType
     * @param bool $forcePathDetection
     * @return array
     */
    public function findClosestInheritanceParent($elementId, $cType, $forcePathDetection = false)
    {
        $type = 'document';
        if ($cType == 'object') {
            $type = 'object';
        } elseif ($cType == 'asset') {
            $type = 'asset';
        }

        $parentPath = null;
        $parentKey = null;
        $parentId = null;
        $restriction = null;

        $obj = Model\Element\Service::getElementById($type, $elementId);

        if ($obj instanceof Model\AbstractModel) {

            $currentRestriction = false;
            try {
                $currentRestriction = Restriction::getByTargetId($obj->getId(), $cType);
            } catch (\Exception $e) {
            }

            if ($forcePathDetection === true || ($currentRestriction instanceof Restriction && $currentRestriction->getIsInherited() === true)) {

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

                if ($obj instanceof Model\DataObject\AbstractObject) {
                    $class = '\Pimcore\Model\DataObject\AbstractObject';
                } elseif ($obj instanceof Model\Document) {
                    $class = '\Pimcore\Model\Document';
                } elseif ($obj instanceof Model\Asset) {
                    $class = '\Pimcore\Model\Asset';
                }

                foreach ($paths as $p) {

                    /** @var \Pimcore\Model\AbstractModel $el */
                    if ($el = $class::getByPath($p)) {

                        $restriction = false;
                        try {
                            $restriction = Restriction::getByTargetId($el->getId(), $cType);
                        } catch (\Exception $e) {
                        }

                        if ($restriction instanceof Restriction && ($restriction->getInherit() === true || $restriction->getIsInherited() === true)) {
                            $parentPath = $el->getFullPath();
                            $parentKey = $el->getKey();
                            $parentId = $el->getId();
                            break;
                        }
                    }
                }
            }
        }

        return [
            'path'        => $parentPath,
            'key'         => $parentKey,
            'id'          => $parentId,
            'restriction' => $restriction
        ];
    }
}