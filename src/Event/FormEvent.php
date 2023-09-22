<?php

namespace MembersBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormEvent extends Event
{
    private FormInterface $form;
    private Request $request;
    private ?Response $response = null;

    public function __construct(FormInterface $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}
