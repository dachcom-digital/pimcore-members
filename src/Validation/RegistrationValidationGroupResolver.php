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

namespace MembersBundle\Validation;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationValidationGroupResolver implements ValidationGroupResolverInterface
{
    public function __construct(
        protected ?array $defaultValidationGroups,
        protected string $authIdentifier,
        protected bool $onlyAuthIdentifierRegistration,
        protected RequestStack $requestStack
    ) {
    }

    public function __invoke(FormInterface $form): array
    {
        if ($this->onlyAuthIdentifierRegistration === false) {
            return $this->isSSOAwareForm() ? ['SSO'] : ($this->defaultValidationGroups ?? []);
        }

        if ($this->authIdentifier === 'username') {
            return $this->isSSOAwareForm() ? ['SSOUsernameOnly'] : ['UsernameOnlyRegistration'];
        }

        if ($this->authIdentifier === 'email') {
            return $this->isSSOAwareForm() ? ['SSOEmailOnly'] : ['EmailOnlyRegistration'];
        }

        return $this->defaultValidationGroups ?? [];
    }

    protected function isSSOAwareForm(): bool
    {
        return $this->requestStack->getMainRequest()->attributes->get('_members_sso_aware', null) === true;
    }
}
