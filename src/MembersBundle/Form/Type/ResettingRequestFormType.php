<?php

namespace MembersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResettingRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('username', null, [
            'label'       => 'members.resetting.request.username',
            'constraints' => [new NotBlank()]
        ])->add('submit', SubmitType::class, [
            'label' => 'members.resetting.request.submit',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'members_user_resetting_request';
    }
}
