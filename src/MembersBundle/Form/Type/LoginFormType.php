<?php

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
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('_username', null, [
            'label'       => 'members.auth.login.username',
            'constraints' => [new NotBlank()],
            'data' => $options['last_username']
        ])->add('_password', PasswordType::class, [
            'label'       => 'members.auth.login.password',
            'constraints' => [new NotBlank()],
        ])->add('_remember_me', CheckboxType::class, [
            'label'    => 'members.auth.login.remember_me',
            'required' => false
        ])->add('_submit', SubmitType::class, [
            'label' => 'members.auth.login.submit'
        ]);

        if ($options['_target_path'] !== null) {
            $builder->add('_target_path', HiddenType::class,
                ['data' => $options['_target_path']]
            );
        }
        if ($options['_failure_path'] !== null) {
            $builder->add('_failure_path', HiddenType::class,
                ['data' => $options['_failure_path']]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_token_id'   => 'authenticate',
            'csrf_field_name' => '_csrf_token',
            'last_username' => null,
            '_target_path'    => null,
            '_failure_path'   => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'members_user_login';
    }
}
