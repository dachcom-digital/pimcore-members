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

namespace MembersBundle\Form\Type\Sso;

use MembersBundle\Adapter\User\UserInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompleteProfileFormType extends AbstractType
{
    public function __construct(protected string $class)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var UserInterface $user */
        $user = $builder->getData();

        $builder
            ->add('email', EmailType::class, [
                'label'    => 'members.form.email',
                'disabled' => !empty($user->getEmail())
            ])
            ->add('username', null, [
                'label' => 'members.form.username', 'empty_data' => ''
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'members.form.password'],
                'second_options'  => ['label' => 'members.form.password_confirmation'],
                'invalid_message' => 'members.validation.password.mismatch',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'members.oauth.sso.complete_profile.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'complete_profile'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_sso_identity_complete_profile';
    }
}
