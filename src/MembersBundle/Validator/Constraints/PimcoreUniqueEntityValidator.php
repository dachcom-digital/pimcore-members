<?php

namespace MembersBundle\Validator\Constraints;

use MembersBundle\Manager\UserManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PimcoreUniqueEntityValidator extends ConstraintValidator
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
     * @param \Pimcore\Model\DataObject\MembersUser $entity
     * @param Constraint                            $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof PimcoreUniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\PimcoreUniqueEntity');
        }

        if (!is_array($constraint->fields) && !is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        $fields = (array)$constraint->fields;


        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        if (null === $entity) {
            return;
        }

        $errorPath = $fields[0];

        $criteria = [];
        foreach ($fields as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            if (!method_exists($entity, $getter)) {
                throw new ConstraintDefinitionException(sprintf('The field "%s" is not mapped by Concrete, so it cannot be validated for uniqueness.', $fieldName));
            }

            $criteria[$fieldName] = $entity->$getter();
        }


        $condition = [];
        $values = [];
        foreach ($criteria as $criteriaName => $criteriaValue) {
            $condition[] = $criteriaName . ' = ?';
            $values[] = $criteriaValue;
        }

        $resultEntity = $this->userManager->findUserByCondition(implode(' AND ', $condition), $values);

        if (null === $resultEntity || ($resultEntity && $resultEntity->getId() === $entity->getId())) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath($errorPath)
            ->setParameter('{{ value }}', $criteria[$fields[0]])
            ->setInvalidValue($criteria[$fields[0]])
            ->setCause($entity)
            ->addViolation();
    }
}