<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->buildUserForm($builder, $options);

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
                'constraints' => [new NotBlank(), new UserPassword($constraintsOptions)],
            ])->add('submit', SubmitType::class, [
                'label' => 'members.profile.edit.submit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'profile'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_profile';
    }

    protected function buildUserForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, ['label' => 'members.form.username'])
            ->add('email', EmailType::class, ['label' => 'members.form.email']);
    }
}
