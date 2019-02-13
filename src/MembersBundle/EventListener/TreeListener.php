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
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
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

    /**
     * @param GenericEvent $event
     */
    public function handleObjectTree(GenericEvent $event)
    {
        $objects = $event->getArgument('objects');

        $restrictionConfig = $this->configuration->getConfig('restriction');
        $allowedTypes = $restrictionConfig['allowed_objects'];

        foreach ($objects as &$object) {
            if (!isset($object['className'])) {
                continue;
            }

            if (!in_array($object['className'], $allowedTypes)) {
                continue;
            }

            $restriction = $this->getRestriction($object['id'], 'object');
            if ($restriction === false) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';

            $currentClass = isset($asset['cls']) ? $object['cls'] : '';
            $object['cls'] = join(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('objects', $objects);
    }

    /**
     * @param GenericEvent $event
     */
    public function handleAssetTree(GenericEvent $event)
    {
        $assets = $event->getArgument('assets');

        foreach ($assets as &$asset) {
            if (!isset($asset['basePath'])) {
                continue;
            }

            if (strpos($asset['basePath'], RestrictionUri::PROTECTED_ASSET_FOLDER) === false) {
                continue;
            }

            $restriction = $this->getRestriction($asset['id'], 'asset');
            if ($restriction === false) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';
            $currentClass = isset($asset['cls']) ? $asset['cls'] : '';
            $asset['cls'] = join(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('assets', $assets);
    }

    /**
     * @param GenericEvent $event
     */
    public function handleDocumentTree(GenericEvent $event)
    {
        $documents = $event->getArgument('documents');

        foreach ($documents as &$document) {
            $restriction = $this->getRestriction($document['id'], 'page');
            if ($restriction === false) {
                continue;
            }

            $lockClass = $restriction->isInherited() ? 'members-locked-inherit' : 'members-locked-main';
            $currentClass = isset($document['cls']) ? $document['cls'] : '';
            $document['cls'] = join(' ', [$currentClass, 'members-locked', $lockClass]);
        }

        $event->setArgument('documents', $documents);
    }

    /**
     * @param int    $id
     * @param string $type
     *
     * @return bool|Restriction
     */
    private function getRestriction($id, $type)
    {
        $restriction = false;

        try {
            $restriction = Restriction::getByTargetId($id, $type);
        } catch (\Exception $e) {
            // fail silently
        }

        return $restriction;
    }
}
