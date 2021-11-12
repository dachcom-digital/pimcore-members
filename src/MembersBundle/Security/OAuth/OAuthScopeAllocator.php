<?php

namespace MembersBundle\Security\OAuth;

class OAuthScopeAllocator implements OAuthScopeAllocatorInterface
{
    protected array $scopes;

    public function __construct(array $scopes)
    {
        $this->scopes = $scopes;
    }

    public function allocate(string $client): array
    {
        if (!isset($this->scopes[$client])) {
            return [];
        }

        if (!is_array($this->scopes[$client])) {
            return [];
        }

        return $this->scopes[$client];
    }
}
