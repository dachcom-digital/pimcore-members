<?php

namespace MembersBundle\Session;

use Pimcore\Session\SessionConfiguratorInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionConfigurator implements SessionConfiguratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(SessionInterface $session)
    {
        $bag = new NamespacedAttributeBag('_members_session');
        $bag->setName('members_session');
        $session->registerBag($bag);
    }
}
