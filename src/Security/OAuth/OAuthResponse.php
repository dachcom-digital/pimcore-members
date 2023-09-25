<?php

namespace MembersBundle\Security\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class OAuthResponse implements OAuthResponseInterface
{
    public function __construct(
        protected string $provider,
        protected AccessToken $accessToken,
        protected ResourceOwnerInterface $resourceOwner,
        protected array $parameter = []
    ) {
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }

    public function getResourceOwner(): ResourceOwnerInterface
    {
        return $this->resourceOwner;
    }

    public function getParameter(): array
    {
        return $this->parameter;
    }
}
