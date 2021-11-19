<?php

namespace MembersBundle\Form\Factory;

use MembersBundle\Validation\ValidationGroupResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormFactory implements FactoryInterface
{
    private FormFactoryInterface $formFactory;
    private string $name;
    private string $type;
    private null|array|ValidationGroupResolverInterface $validationGroups;

    public function __construct(FormFactoryInterface $formFactory, string $name, string $type, null|array|ValidationGroupResolverInterface $validationGroups = null)
    {
        $this->formFactory = $formFactory;
        $this->name = $name;
        $this->type = $type;
        $this->validationGroups = $validationGroups;
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
