<?php

namespace MembersBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class GetResponseNullableUserEvent extends GetResponseUserEvent
{
    /**
     * GetResponseNullableUserEvent constructor.
     *
     * @param UserInterface|null $user
     * @param Request            $request
     */
    public function __construct(UserInterface $user = null, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }
}
