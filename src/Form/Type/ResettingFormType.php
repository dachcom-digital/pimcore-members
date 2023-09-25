<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResettingFormType extends AbstractType
{
    public function __construct(private string $class)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'members.form.new_password'],
                'second_options'  => ['label' => 'members.form.new_password_confirmation'],
                'invalid_message' => 'members.validation.password.mismatch',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'members.resetting.reset.submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'resetting'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_resetting';
    }
}
