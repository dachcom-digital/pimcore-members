<?php

namespace MembersBundle\Restriction;

use Pimcore\Model;

class RestrictionService
{
    const ALLOWED_RESTRICTION_CTYPES = ['asset', 'page', 'object'];

    /**
     * @param Model\Element\ElementInterface $obj
     * @param string                         $cType
     * @param bool                           $inheritable
     * @param bool                           $isInherited
     * @param array                          $userGroupIds
     *
     * @return Restriction|null
     * @throws \Exception
     */
    public function createRestriction(Model\Element\ElementInterface $obj, string $cType, bool $inheritable = false, bool $isInherited = false, array $userGroupIds = [])
    {
        if(!in_array($cType, self::ALLOWED_RESTRICTION_CTYPES)) {
            throw new \Exception(sprintf('restriction cType needs to be one of these: %s', implode(', ', self::ALLOWED_RESTRICTION_CTYPES)));
        }
        $restriction = null;
        $hasRestriction = true;

        try {
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            $hasRestriction = false;
        }

        //remove restriction since no group is selected any more.
        if (empty($userGroupIds)) {
            if ($hasRestriction === true) {
                $restriction->delete();
            }
        } else {
            if ($hasRestriction === false) {
                $restriction = new Restriction();
                $restriction->setTargetId($obj->getId());
                $restriction->setCtype($cType);
            }

            $restriction->setInherit($inheritable);
            $restriction->setIsInherited($isInherited);
            $restriction->setRelatedGroups($userGroupIds);
            $restriction->save();
        }

        $this->checkRestrictionContext($obj, $cType);

        //clear cache!
        \Pimcore\Cache::clearTag('members');

        return $restriction;
    }

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
     * @param Model\Element\ElementInterface $obj
     * @param string                         $cType
     */
    public function checkRestrictionContext($obj, $cType)
    {
        $restriction = null;
        $parentRestriction = null;

        try {
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            // fail silently
        }

        //get closest inherit object with restriction
        $closestInheritanceParent = $this->findClosestInheritanceParent($obj->getId(), $cType);
        if (!is_null($closestInheritanceParent['id'])) {
            $parentRestriction = $closestInheritanceParent['restriction'];
        }

        if ($this->onlyUpdateChildren($obj) === false) {
            $this->updateRestrictionContext($obj, $cType, $restriction, $parentRestriction);
        }

        $this->updateChildren($obj, $cType);

    }

    /**
     * @param $obj
     * @param $cType
     */
    private function updateChildren($obj, $cType)
    {
        $list = null;
        if ($obj instanceof Model\DataObject\AbstractObject) {
            $list = new Model\DataObject\Listing();
            $list->setCondition('o_type = ? AND o_path LIKE ?', ['object', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(o_path) ASC', false);
        } elseif ($obj instanceof Model\Document) {
            $list = new Model\Document\Listing();
            $list->setCondition('type IN ("page", "link") AND path LIKE ?', [$obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        } elseif ($obj instanceof Model\Asset && $obj->getType() === 'folder') {
            $list = new Model\Asset\Listing();
            $list->setCondition('path LIKE ?', [$obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        }

        if ($list === null) {
            return;
        }

        $children = $list->load();

        if (empty($children)) {
            return;
        }

        /** @var \Pimcore\Model\AbstractModel $child */
        foreach ($children as $child) {

            $childRestriction = null;
            $parentRestriction = null;

            try {
                $childRestriction = Restriction::getByTargetId($child->getId(), $cType);
            } catch (\Exception $e) {
            }

            $closestInheritanceParent = $this->findClosestInheritanceParent($child->getId(), $cType);
            if (!is_null($closestInheritanceParent['id'])) {
                $parentRestriction = $closestInheritanceParent['restriction'];
            }

            $this->updateRestrictionContext($child, $cType, $childRestriction, $parentRestriction);
        }

    }

    /**
     * @param      $elementId
     * @param      $cType
     *
     * @return array
     */
    public function findClosestInheritanceParent($elementId, $cType)
    {
        $type = 'document';
        if ($cType === 'object') {
            $type = 'object';
        } elseif ($cType === 'asset') {
            $type = 'asset';
        }

        $data = [
            'path'        => null,
            'key'         => null,
            'id'          => null,
            'restriction' => null
        ];

        $parentPath = null;
        $parentKey = null;
        $parentId = null;
        $restriction = null;
        $currentRestriction = false;

        $obj = Model\Element\Service::getElementById($type, $elementId);

        if (!$obj instanceof Model\AbstractModel) {
            return $data;
        }

        try {
            $currentRestriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
        }

        if ($currentRestriction instanceof Restriction && $currentRestriction->getIsInherited() === false) {
            return $data;
        }

        $path = urldecode($obj->getRealPath());

        $paths = ['/'];
        $tmpPaths = [];
        $pathParts = array_filter(explode('/', $path));
        foreach ($pathParts as $pathPart) {
            $tmpPaths[] = $pathPart;
            $t = '/' . implode('/', $tmpPaths);
            if (!empty($t)) {
                $paths[] = $t;
            }
        }

        $paths = array_reverse($paths);

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

                if ($restriction instanceof Restriction) {
                    if ($restriction->getInherit() === true || $restriction->getIsInherited() === true) {
                        $parentPath = $el->getFullPath();
                        $parentKey = $el->getKey();
                        $parentId = $el->getId();
                    }
                    break;
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

    /**
     * @param                  $obj
     * @param                  $cType
     * @param Restriction|null $objectRestriction
     * @param Restriction|null $parentRestriction
     */
    private function updateRestrictionContext($obj, $cType, $objectRestriction, $parentRestriction)
    {
        $hasRestriction = $objectRestriction instanceof Restriction;
        $hasParentRestriction = $parentRestriction instanceof Restriction;

        if (!$hasParentRestriction && !$hasRestriction) {
            return;
        }

        if ($hasParentRestriction && !$hasRestriction) {
            $restriction = new Restriction();
            $restriction->setTargetId($obj->getId());
            $restriction->setCtype($cType);
            $restriction->setIsInherited(true);
            $restriction->setRelatedGroups($parentRestriction->getRelatedGroups());
            $restriction->save();
            return;
        }

        if (!$hasParentRestriction && $hasRestriction) {
            if ($objectRestriction->isInherited()) {
                $objectRestriction->delete();
                return;
            }
        }

        if ($hasParentRestriction && $hasRestriction) {
            if ($objectRestriction->isInherited()) {
                if ($parentRestriction->getInherit() === false && $parentRestriction->isInherited() === false) {
                    $objectRestriction->delete();
                } else {
                    $objectRestriction->setRelatedGroups($parentRestriction->getRelatedGroups());
                    $objectRestriction->save();
                }
                return;
            }
        }
    }

    /**
     * @param $obj
     *
     * @return bool
     */
    private function onlyUpdateChildren($obj)
    {
        if ($obj instanceof Model\DataObject\AbstractObject) {
            return $obj->getType() === 'folder';
        } elseif ($obj instanceof Model\Document) {
            return !in_array($obj->getType(), ['page', 'link']);
        } elseif ($obj instanceof Model\Asset) {
            return false;
        }
    }
}