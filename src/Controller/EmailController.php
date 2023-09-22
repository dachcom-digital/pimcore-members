<?php

namespace MembersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends AbstractController
{
    public function emailAction(Request $request): Response
    {
        return $this->renderTemplate('@Members/email/email.html.twig', array_filter($request->attributes->all(), static function($parameterKey) {
            return !str_starts_with($parameterKey, '_');
        }, ARRAY_FILTER_USE_KEY));
    }
}
