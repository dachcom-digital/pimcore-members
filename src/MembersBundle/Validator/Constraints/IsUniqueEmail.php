<?php

namespace MembersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class IsUniqueEmail extends Constraint
{
    /**
     * @var string
     */
    public $message = 'members.validation.email_already_used';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'members.constraint.email_already_exist';
    }

}
