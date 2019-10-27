<?php

namespace MembersBundle\Security\OAuth;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

class OAuthResponse implements OAuthResponseInterface
{
    /**
     * @var string
     */
    protected $provider;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * @var ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @param string                 $provider
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function __construct(string $provider, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        $this->provider = $provider;
        $this->accessToken = $accessToken;
        $this->resourceOwner = $resourceOwner;
    }

    /**
     * {@inheritDoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }
}
