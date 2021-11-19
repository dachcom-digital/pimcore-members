<?php

namespace MembersBundle\Form\Extension;

use MembersBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RegistrationAuthIdentifierTypeExtension extends AbstractTypeExtension
{
    protected string $authIdentifier;
    protected bool $onlyAuthIdentifierRegistration;

    public function __construct(string $authIdentifier, bool $onlyAuthIdentifierRegistration)
    {
        $this->authIdentifier = $authIdentifier;
        $this->onlyAuthIdentifierRegistration = $onlyAuthIdentifierRegistration;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->onlyAuthIdentifierRegistration === false) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            if ($this->authIdentifier === 'username' && $form->has('email')) {
                $form->remove('email');
            } elseif ($this->authIdentifier === 'email' && $form->has('username')) {
                $form->remove('username');
            }
        });
    }

    public function getExtendedType(): string
    {
        return RegistrationFormType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [RegistrationFormType::class];
    }
}
