<?php

namespace MembersBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;

interface FactoryInterface
{
    /**
     * @param array $options
     *
     * @return FormInterface
     */
    public function createForm(array $options = []);

    /**
     * @return FormInterface
     */
    public function createUnnamedForm();

    /**
     * @param array $options
     *
     * @return FormInterface
     */
    public function createUnnamedFormWithOptions(array $options);
}
