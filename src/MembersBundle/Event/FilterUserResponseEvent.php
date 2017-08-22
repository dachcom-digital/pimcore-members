<?php

namespace MembersBundle\Event;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserResponseEvent extends UserEvent
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * FilterUserResponseEvent constructor.
     *
     * @param UserInterface $user
     * @param Request       $request
     * @param Response      $response
     */
    public function __construct(UserInterface $user, Request $request, Response $response)
    {
        parent::__construct($user, $request);
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
