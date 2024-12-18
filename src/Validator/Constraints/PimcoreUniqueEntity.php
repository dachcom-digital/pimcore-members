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
