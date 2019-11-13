<?php

namespace MembersBundle\Security\OAuth;

class OAuthScopeAllocator implements OAuthScopeAllocatorInterface
{
    /**
     * @var array
     */
    protected $scopes;

    /**
     * @param array $scopes
     */
    public function __construct(array $scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * {@inheritDoc}
     */
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
