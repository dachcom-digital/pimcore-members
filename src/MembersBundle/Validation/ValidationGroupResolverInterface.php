<?php

namespace MembersBundle\Validation;

use Symfony\Component\Form\FormInterface;

interface ValidationGroupResolverInterface
{
    public function __invoke(FormInterface $form): array;
}