<?php

namespace MembersBundle\Form\Extension;

use MembersBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationTypeExtension extends AbstractTypeExtension
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($this->isSSOAwareForm() === false) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            if ($form->has('plainPassword')) {
                $form->remove('plainPassword');
            }
        });
    }

    protected function isSSOAwareForm(): bool
    {
        return $this->requestStack->getMainRequest()->attributes->get('_members_sso_aware', null) === true;
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
