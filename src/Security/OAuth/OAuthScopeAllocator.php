<?php

namespace MembersBundle\Security\OAuth;

class OAuthScopeAllocator implements OAuthScopeAllocatorInterface
{
    public function __construct(protected array $scopes)
    {
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
