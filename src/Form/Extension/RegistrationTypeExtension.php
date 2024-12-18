<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

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
