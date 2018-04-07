<?php

namespace MembersBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;

interface FactoryInterface
{
    /**
     * @return FormInterface
     */
    public function createForm();

    /**
     * @return FormInterface
     */
    public function createUnnamedForm();
}