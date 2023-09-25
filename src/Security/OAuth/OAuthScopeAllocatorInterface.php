<?php

namespace MembersBundle\Security\OAuth;

interface OAuthScopeAllocatorInterface
{
    public function allocate(string $client): array;
}
