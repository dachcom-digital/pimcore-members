<?php

namespace MembersBundle\Event;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserResponseEvent extends UserEvent
{
    protected Response $response;

    public function __construct(UserInterface $user, Request $request, Response $response)
    {
        parent::__construct($user, $request);

        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
