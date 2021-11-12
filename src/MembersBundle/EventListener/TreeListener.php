<?php

namespace MembersBundle\EventListener;

use MembersBundle\Configuration\Configuration;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Event\AdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class TreeListener implements EventSubscriberInterface
{
    protected Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public static function getSubscribedEvents(): array
    {
        $defaultEvents = [
            AdminEvents::OBJECT_TREE_GET_CHILDREN_BY_ID_PRE_SEND_DATA => ['handleObjectTree', 0]
        ];

        if (defined('\Pimcore\Event\AdminEvents::ASSET_TREE_GET_CHILDREN_BY_ID_PRE_SEND_DATA')) {
            $defaultEvents[AdminEvents::ASSET_TREE_GET_CHILDREN_BY_ID_PRE_SEND_DATA] = ['handleAssetTree', 0];
        }

        if (defined('\Pimcore\Event\AdminEvents::DOCUMENT_TREE_GET_CHILDREN_BY_ID_PRE_SEND_DATA')) {
            $defaultEvents[AdminEvents::DOCUMENT_TREE_GET_CHILDREN_BY_ID_PRE_SEND_DATA] = ['handleDocumentTree', 0];
        }

        return $defaultEvents;
    }

    public function handleObjectTree(GenericEvent $event): void
    {
        $objects = $event->getArgument('objects');

        $restrictionConfig = $this->configuration->getConfig('restriction');
        $allowedTypes = $restrictionConfig['allowed_objects'];

        foreach ($objects as &$object) {
            if (!isset($object['className'])) {
                continue;
            }

            if (!in_array($object['className'], $allowedTypes, true)) {
                continue;
            }

            $restriction = $this->getRestriction($object['id'], 'object');
            if (!$restriction instanceof Restriction) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';

            $currentClass = $object['cls'] ?? '';
            $object['cls'] = implode(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('objects', $objects);
    }

    public function handleAssetTree(GenericEvent $event): void
    {
        $assets = $event->getArgument('assets');

        foreach ($assets as &$asset) {
            if (!isset($asset['basePath'])) {
                continue;
            }

            if (!str_contains($asset['basePath'], RestrictionUri::PROTECTED_ASSET_FOLDER)) {
                continue;
            }

            $restriction = $this->getRestriction($asset['id'], 'asset');
            if (!$restriction instanceof Restriction) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';
            $currentClass = $asset['cls'] ?? '';
            $asset['cls'] = implode(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('assets', $assets);
    }

    public function handleDocumentTree(GenericEvent $event): void
    {
        $documents = $event->getArgument('documents');

        foreach ($documents as &$document) {
            $restriction = $this->getRestriction($document['id'], 'page');
            if (!$restriction instanceof Restriction) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';
            $currentClass = $document['cls'] ?? '';
            $document['cls'] = implode(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('documents', $documents);
    }

    private function getRestriction(int $id, string $type): ?Restriction
    {
        try {
            $restriction = Restriction::getByTargetId($id, $type);
        } catch (\Exception $e) {
            return null;
        }

        return $restriction;
    }
}
