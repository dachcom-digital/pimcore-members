<?php

namespace MembersBundle\Service;

use MembersBundle\Event\RestrictionEvent;
use MembersBundle\MembersEvents;
use Pimcore\Cache;
use Pimcore\Model;
use Pimcore\Model\Element\ElementInterface;
use MembersBundle\Restriction\Restriction;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RestrictionService
{
    public const ALLOWED_RESTRICTION_CTYPES = ['asset', 'page', 'object'];

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \Exception
     */
    public function createRestriction(ElementInterface $obj, string $cType, bool $inheritable = false, bool $isInherited = false, array $userGroupIds = []): ?Restriction
    {
        if (!in_array($cType, self::ALLOWED_RESTRICTION_CTYPES)) {
            throw new \Exception(sprintf('restriction cType needs to be one of these: %s', implode(', ', self::ALLOWED_RESTRICTION_CTYPES)));
        }

        $isUpdate = true;
        $restriction = null;

        try {
            $restriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            // fail silently
        }

        if (empty($userGroupIds)) {

            // remove restriction if no groups have been assigned
            // and restriction is not inherited

            if ($restriction instanceof Restriction && $restriction->isInherited() === false) {

                $restriction->getDao()->delete();

                $this->triggerEvent($obj, $restriction, MembersEvents::ENTITY_DELETE_RESTRICTION);
                $this->checkRestrictionContext($obj, $cType);
            }

            return null;
        }

        if (!$restriction instanceof Restriction) {
            $isUpdate = false;
            $restriction = new Restriction();
            $restriction->setTargetId($obj->getId());
            $restriction->setCtype($cType);
        }

        $restriction->setInherit($inheritable);
        $restriction->setIsInherited($isInherited);
        $restriction->setRelatedGroups($userGroupIds);
        $restriction->getDao()->save();

        $this->triggerEvent($obj, $restriction, $isUpdate === true ? MembersEvents::ENTITY_UPDATE_RESTRICTION : MembersEvents::ENTITY_CREATE_RESTRICTION);
        $this->checkRestrictionContext($obj, $cType);

        return $restriction;
    }

    /**
     * Triggered by pre deletion events of all types.
     */
    public function deleteRestriction(ElementInterface $obj, string $cType): void
    {
        $docId = $obj->getId();
        $restriction = null;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
            // fail silently
        }

        if (!$restriction instanceof Restriction) {
            return;
        }

        $restriction->getDao()->delete();

        $this->triggerEvent($obj, $restriction, MembersEvents::ENTITY_DELETE_RESTRICTION);
    }

    /**
     * Triggered by post update events of all types ONLY when element gets moved in tree!
     * Check if element is in right context.
     */
    public function checkRestrictionContext(ElementInterface $obj, string $cType): void
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

    private function updateChildren(ElementInterface $obj, string $cType): void
    {
        $list = null;
        if ($obj instanceof Model\DataObject) {
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

        /** @var ElementInterface $child */
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

    public function findClosestInheritanceParent(int $elementId, string $cType): array
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

        if (!$obj instanceof ElementInterface) {
            return $data;
        }

        try {
            $currentRestriction = Restriction::getByTargetId($obj->getId(), $cType);
        } catch (\Exception $e) {
            // fail silently
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

        $class = null;
        if ($obj instanceof Model\DataObject) {
            $class = '\Pimcore\Model\DataObject';
        } elseif ($obj instanceof Model\Document) {
            $class = '\Pimcore\Model\Document';
        } elseif ($obj instanceof Model\Asset) {
            $class = '\Pimcore\Model\Asset';
        }

        if ($class === null) {
            return $data;
        }

        if (!method_exists($class, 'getByPath')) {
            throw new \Exception(sprintf('Method "getByPath" in class "%s" not found', get_class($class)));
        }

        foreach ($paths as $p) {

            /** @var ElementInterface $el */
            if ($el = $class::getByPath($p)) {
                $restriction = false;

                try {
                    $restriction = Restriction::getByTargetId($el->getId(), $cType);
                } catch (\Exception $e) {
                    // fail silently?
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

    protected function updateRestrictionContext(ElementInterface $obj, string $cType, ?Restriction $objectRestriction, ?Restriction $parentRestriction): void
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
            $restriction->getDao()->save();

            $this->triggerEvent($obj, $restriction, MembersEvents::ENTITY_UPDATE_RESTRICTION);

            return;
        }

        if (!$hasParentRestriction && $hasRestriction) {
            if ($objectRestriction->isInherited()) {

                $objectRestriction->getDao()->delete();

                $this->triggerEvent($obj, $objectRestriction, MembersEvents::ENTITY_DELETE_RESTRICTION);

                return;
            }
        }

        if ($hasParentRestriction && $hasRestriction) {
            if ($objectRestriction->isInherited()) {
                if ($parentRestriction->getInherit() === false && $parentRestriction->isInherited() === false) {
                    $objectRestriction->getDao()->delete();

                    $this->triggerEvent($obj, $objectRestriction, MembersEvents::ENTITY_DELETE_RESTRICTION);
                } else {
                    $objectRestriction->setRelatedGroups($parentRestriction->getRelatedGroups());
                    $objectRestriction->getDao()->save();

                    $this->triggerEvent($obj, $objectRestriction, MembersEvents::ENTITY_UPDATE_RESTRICTION);
                }
            }
        }
    }

    protected function onlyUpdateChildren(ElementInterface $obj): bool
    {
        if ($obj instanceof Model\DataObject) {
            return $obj->getType() === 'folder';
        }

        if ($obj instanceof Model\Document) {
            return !in_array($obj->getType(), ['page', 'link']);
        }

        if ($obj instanceof Model\Asset) {
            return false;
        }

        return true;
    }

    protected function triggerEvent(ElementInterface $obj, ?Restriction $restriction, string $eventName): void
    {
        $this->eventDispatcher->dispatch(new RestrictionEvent($obj, $restriction), $eventName);

        $this->clearMembersCacheTags();
    }

    protected function clearMembersCacheTags(): void
    {
        Cache::clearTag('members');
    }
}
