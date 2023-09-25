<?php

namespace MembersBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;

interface FactoryInterface
{
    public function createForm(array $options = []): FormInterface;

    public function createUnnamedForm(): FormInterface;

    public function createUnnamedFormWithOptions(array $options): FormInterface;
}
