<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordFormType extends AbstractType
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraintsOptions = [
            'message' => 'members.validation.current_password.invalid',
        ];

        if (!empty($options['validation_groups'])) {
            $constraintsOptions['groups'] = [reset($options['validation_groups'])];
        }

        $builder
            ->add('current_password', PasswordType::class, [
                'label'       => 'members.form.current_password',
                'mapped'      => false,
                'constraints' => new UserPassword($constraintsOptions),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'members.form.new_password'],
                'second_options'  => ['label' => 'members.form.new_password_confirmation'],
                'invalid_message' => 'members.validation.password.mismatch',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'members.change_password.submit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'change_password'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_change_password';
    }
}
