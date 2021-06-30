<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    protected string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'members.form.email'
            ]);

        $builder->add('username', null, ['label' => 'members.form.username']);

        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'first_options'   => ['label' => 'members.form.password'],
            'second_options'  => ['label' => 'members.form.password_confirmation'],
            'invalid_message' => 'members.validation.password.mismatch',
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'members.registration.submit',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'registration'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_registration';
    }
}
