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

class RegistrationAuthIdentifierTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        protected string $authIdentifier,
        protected bool $onlyAuthIdentifierRegistration
    ) {
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
