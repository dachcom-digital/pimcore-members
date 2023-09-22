<?php

namespace MembersBundle\Security\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

interface OAuthResponseInterface
{
    public function getProvider(): string;

    public function getAccessToken(): AccessToken;

    public function getResourceOwner(): ResourceOwnerInterface;

    public function getParameter(): array;
}
