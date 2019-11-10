<?php

namespace MembersBundle\Security\OAuth;

interface OAuthTokenStorageInterface
{
    /**
     * @param string                 $key
     * @param OAuthResponseInterface $OAuthResponse
     */
    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse);

    /**
     * @param string $key
     */
    public function destroyToken(string $key);

    /**
     * @param string $key
     * @param int    $maxLifetime
     *
     * @return OAuthResponseInterface|null
     */
    public function loadToken(string $key, int $maxLifetime = 300);
}
