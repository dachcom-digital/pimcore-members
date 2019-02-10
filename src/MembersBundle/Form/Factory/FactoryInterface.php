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

    /**
     * @param array $option
     *
     * @return FormInterface
     */
    public function createUnnamedFormWithOption(array $option);
}