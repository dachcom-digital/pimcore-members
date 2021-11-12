<?php

namespace MembersBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class EmailController extends AbstractController
{
    public function emailAction(): Response
    {
        return $this->renderTemplate('@Member/email/email.html.twig');
    }
}
