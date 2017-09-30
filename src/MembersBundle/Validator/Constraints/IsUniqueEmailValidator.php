<?php

namespace MembersBundle\Validator\Constraints;

use MembersBundle\Manager\UserManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsUniqueEmailValidator extends ConstraintValidator
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * IsUniqueEmailValidator constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (count($this->userManager->findUserByCondition('email = ?', [$value])) > 0) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}