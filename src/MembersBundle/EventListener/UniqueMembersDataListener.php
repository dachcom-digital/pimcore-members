<?php

namespace MembersBundle\EventListener;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\ClassManagerInterface;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UniqueMembersDataListener implements EventSubscriberInterface
{
    /**
     * @var ClassManagerInterface
     */
    protected $classManager;

    /**
     * @var int
     */
    protected $memberStorageId;

    /**
     * {@inheritdoc}
     */
    public function __construct(ClassManagerInterface $classManager)
    {
        $this->classManager = $classManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DataObjectEvents::PRE_UPDATE => 'handleUniqueness'
        ];
    }

    /**
     * @param DataObjectEvent $e
     * @throws \Exception
     */
    public function handleUniqueness(DataObjectEvent $e)
    {
        $object = $e->getObject();

        if ($object instanceof UserInterface) {
            $memberListing = $this->classManager->getUserListing();
            $memberListing->setUnpublished(true);
            $memberListing->setCondition('(email = ? OR userName = ?) AND oo_id != ?', [
                $object->getEmail(),
                $object->getUsername(),
                (int)$object->getId()
            ]);

            $elements = $memberListing->load();

            if (count($elements) > 0) {
                $foundObject = $elements[0];
                $artifact = 'email address';
                if ($foundObject->getUsername() === $object->getUsername()) {
                    $artifact = 'username';
                }
                throw new \Exception(sprintf('The %s is already used.', $artifact));
            }

        } elseif ($object instanceof GroupInterface) {
            $groupListing = $this->classManager->getGroupListing();
            $groupListing->setCondition('name = ? AND oo_id != ?', [$object->getName(), (int)$object->getId()]);
            $groupListing->setUnpublished(true);
            $elements = $groupListing->load();
            if (count($elements) > 0) {
                throw new \Exception('The group name is already used.');
            }
        }
    }
}