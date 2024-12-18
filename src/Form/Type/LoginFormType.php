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

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('_username', null, [
                'label'       => 'members.auth.login.username',
                'constraints' => [new NotBlank()],
                'data'        => $options['last_username']
            ])
            ->add('_password', PasswordType::class, [
                'label'       => 'members.auth.login.password',
                'constraints' => [new NotBlank()],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label'    => 'members.auth.login.remember_me',
                'required' => false
            ])
            ->add('_submit', SubmitType::class, [
                'label' => 'members.auth.login.submit'
            ]);

        if ($options['_target_path'] !== null) {
            $builder->add(
                '_target_path',
                HiddenType::class,
                ['data' => $options['_target_path']]
            );
        }
        if ($options['_failure_path'] !== null) {
            $builder->add(
                '_failure_path',
                HiddenType::class,
                ['data' => $options['_failure_path']]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id'   => 'authenticate',
            'csrf_field_name' => '_csrf_token',
            'last_username'   => null,
            '_target_path'    => null,
            '_failure_path'   => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_login';
    }
}
