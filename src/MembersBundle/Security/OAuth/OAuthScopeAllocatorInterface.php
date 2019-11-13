<?php

namespace MembersBundle\Security\OAuth;

interface OAuthScopeAllocatorInterface
{
    /**
     * @param string $client
     *
     * @return array
     */
    public function allocate(string $client): array;
}
