<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Validator\Constraints;

use MembersBundle\Manager\UserManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PimcoreUniqueEntityValidator extends ConstraintValidator
{
    public function __construct(protected UserManagerInterface $userManager)
    {
    }

    public function validate($entity, Constraint $constraint): void
    {
        if (!$constraint instanceof PimcoreUniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\PimcoreUniqueEntity');
        }

        $fields = $constraint->fields;

        if (count($fields) === 0) {
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

        if ($resultEntity === null || ($resultEntity->getId() === $entity->getId())) {
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
