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

namespace MembersBundle\Form\Factory;

use MembersBundle\Validation\ValidationGroupResolverInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FormFactory implements FactoryInterface
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private string $name,
        private string $type,
        private null|array|ValidationGroupResolverInterface $validationGroups = null
    ) {
    }

    public function createForm(array $options = []): FormInterface
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed($this->name, $this->type, null, $options);
    }

    public function createUnnamedForm(): FormInterface
    {
        return $this->formFactory->createNamed('', $this->type, null, ['validation_groups' => $this->validationGroups]);
    }

    public function createUnnamedFormWithOptions(array $options = []): FormInterface
    {
        $options = array_merge(['validation_groups' => $this->validationGroups], $options);

        return $this->formFactory->createNamed('', $this->type, null, $options);
    }
}
