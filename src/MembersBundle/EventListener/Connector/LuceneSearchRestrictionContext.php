<?php

namespace MembersBundle\EventListener\Connector;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LuceneSearchRestrictionContext implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * LuceneSearchAssetRestriction constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'lucene_search.frontend.restriction_context' => 'checkContext',
        ];
    }

    /**
     * @param \LuceneSearchBundle\Event\RestrictionContextEvent $event
     */
    public function checkContext(\LuceneSearchBundle\Event\RestrictionContextEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof UserInterface) {
            $allowedGroups = $user->getGroups();

            $groupIds = [];
            if (!empty($allowedGroups)) {
                /** @var GroupInterface $group */
                foreach ($allowedGroups as $group) {
                    $groupIds[] = $group->getId();
                }

                if (!empty($groupIds)) {
                    $event->setAllowedRestrictionGroups($groupIds);
                }
            }
        }
    }
}
