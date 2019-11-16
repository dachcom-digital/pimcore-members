<?php

namespace MembersBundle\Security\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

interface OAuthResponseInterface
{
    /**
     * @return string
     */
    public function getProvider();

    /**
     * @return AccessToken
     */
    public function getAccessToken();

    /**
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner();
}
