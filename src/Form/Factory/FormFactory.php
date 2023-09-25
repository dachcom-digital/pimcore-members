<?php

namespace MembersBundle\Form\Factory;

use MembersBundle\Validation\ValidationGroupResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormFactory implements FactoryInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private string $name,
        private string $type,
        private null|array|ValidationGroupResolverInterface $validationGroups = null
    )
    {
    }

    public function createForm(array $options = []): FormInterface
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed($this->name, $this->type, null, $options);
    }

    public function createUnnamedForm(): FormInterface
    {
        return $this->formFactory->createNamed('', $this->type, null, ['validation_groups' => $this->validationGroups]);
    }

    public function createUnnamedFormWithOptions(array $options = []): FormInterface
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed('', $this->type, null, $options);
    }
}
