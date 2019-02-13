<?php

namespace MembersBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;

class FormFactory implements FactoryInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $validationGroups;

    /**
     * FormFactory constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param string               $name
     * @param string               $type
     * @param array                $validationGroups
     */
    public function __construct(FormFactoryInterface $formFactory, $name, $type, array $validationGroups = null)
    {
        $this->formFactory = $formFactory;
        $this->name = $name;
        $this->type = $type;
        $this->validationGroups = $validationGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(array $options = [])
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed($this->name, $this->type, null, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createUnnamedForm()
    {
        return $this->formFactory->createNamed('', $this->type, null, ['validation_groups' => $this->validationGroups]);
    }

    /**
     * {@inheritdoc}
     */
    public function createUnnamedFormWithOptions(array $options = [])
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed('', $this->type, null, $options);
    }
}
