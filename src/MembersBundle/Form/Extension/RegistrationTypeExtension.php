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
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isSSOAwareForm() === false) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->has('plainPassword') === false) {
                return;
            }

            $form->remove('plainPassword');
        });
    }

    /**
     * @return bool
     */
    protected function isSSOAwareForm()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        return $masterRequest->attributes->get('_members_sso_aware', null) === true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return RegistrationFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [RegistrationFormType::class];
    }
}
