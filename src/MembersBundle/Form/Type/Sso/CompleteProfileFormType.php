<?php

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
    /**
     * @var string
     */
    protected $class;

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
        /** @var UserInterface $user */
        $user = $builder->getData();

        $builder->add('email', EmailType::class, [
            'label'    => 'members.form.email',
            'disabled' => !empty($user->getEmail())
        ]);

        $builder->add('username', null, ['label' => 'members.form.username']);

        $builder->add('plainPassword', RepeatedType::class, [
            'type'            => PasswordType::class,
            'first_options'   => ['label' => 'members.form.password'],
            'second_options'  => ['label' => 'members.form.password_confirmation'],
            'invalid_message' => 'members.validation.password.mismatch',
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'members.oauth.sso.complete_profile.submit',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => $this->class,
            'csrf_token_id' => 'complete_profile'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'members_user_sso_identity_complete_profile';
    }
}
