<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\IsTrue;

class DeleteAccountFormType extends AbstractType
{
    public function __construct(private string $class)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('current_password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => ['label' => 'members.form.current_password'],
                'second_options'  => ['label' => 'members.form.current_password_confirmation'],
                'label'           => 'members.form.current_password',
                'mapped'          => false,
                'invalid_message' => 'members.validation.password.mismatch',
                'constraints'     => [new UserPassword(['message' => 'members.validation.current_password.invalid'])],
            ])
            ->add('deleteConfirm', CheckboxType::class, [
                'label'       => 'members.form.delete_account.confirm',
                'mapped'      => false,
                'constraints' => [new IsTrue(['message' => 'members.validation.delete_account.confirm_not_checked'])],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'members.delete_account.submit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'delete_account'
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_delete_account';
    }
}
