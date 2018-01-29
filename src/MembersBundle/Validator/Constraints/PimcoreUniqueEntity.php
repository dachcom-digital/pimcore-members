<?php

namespace MembersBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class PimcoreUniqueEntity extends Constraint
{
    public $fields = [];

    /**
     * @var string
     */
    public $message = 'members.validation.value_already_used';

    /**
     * @return array
     */
    public function getRequiredOptions()
    {
        return ['fields'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'members.validator.unique';
    }

    public function getDefaultOption()
    {
        return 'fields';
    }

}
