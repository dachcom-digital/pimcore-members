<?php

namespace MembersBundle\Validation;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RegistrationValidationGroupResolver implements ValidationGroupResolverInterface
{
    protected ?array $defaultValidationGroups;
    protected string $authIdentifier;
    protected bool $onlyAuthIdentifierRegistration;
    protected RequestStack $requestStack;

    public function __construct(?array $defaultValidationGroups, string $authIdentifier, bool $onlyAuthIdentifierRegistration, RequestStack $requestStack)
    {
        $this->defaultValidationGroups = $defaultValidationGroups;
        $this->authIdentifier = $authIdentifier;
        $this->onlyAuthIdentifierRegistration = $onlyAuthIdentifierRegistration;
        $this->requestStack = $requestStack;
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