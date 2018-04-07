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
    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class The User class name
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $constraintsOptions = [
            'message' => 'members.validation.current_password.invalid',
        ];

        if (!empty($options['validation_groups'])) {
            $constraintsOptions['groups'] = [reset($options['validation_groups'])];
        }

        $builder->add('current_password', PasswordType::class, [
            'label'       => 'members.form.current_password',
            'mapped'      => false,
            'constraints' => new UserPassword($constraintsOptions),
        ]);

        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'first_options'   => ['label' => 'members.form.new_password'],
            'second_options'  => ['label' => 'members.form.new_password_confirmation'],
            'invalid_message' => 'members.validation.password.mismatch',
        ]);

        $builder->add('submit', SubmitType::class, ['label' => 'members.change_password.submit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'change_password'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'members_user_change_password';
    }
}
