<?php

namespace MembersBundle\Validator\Constraints;

use MembersBundle\Manager\UserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var UserManager
     */
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function validate($value, Constraint $constraint)
    {
        if (count($this->userManager->findUserByCondition('email = ?', [$value])) > 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}