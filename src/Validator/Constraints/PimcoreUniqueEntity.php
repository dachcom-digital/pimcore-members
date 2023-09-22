<?php

namespace MembersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class PimcoreUniqueEntity extends Constraint
{
    public array $fields = [];
    public string $message = 'members.validation.value_already_used';

    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'members.validator.unique';
    }

    public function getDefaultOption(): string
    {
        return 'fields';
    }
}
