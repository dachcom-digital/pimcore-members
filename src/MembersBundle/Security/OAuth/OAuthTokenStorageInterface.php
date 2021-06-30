<?php

namespace MembersBundle\Security\OAuth;

interface OAuthTokenStorageInterface
{
    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse): void;

    public function destroyToken(string $key): void;

    public function loadToken(string $key, int $maxLifetime = 300): ?OAuthResponseInterface;
}
