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
     * @var array
     */
    protected $parameter;

    /**
     * @param string                 $provider
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     * @param array                  $parameter
     */
    public function __construct(string $provider, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner, array $parameter = [])
    {
        $this->provider = $provider;
        $this->accessToken = $accessToken;
        $this->resourceOwner = $resourceOwner;
        $this->parameter = $parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}
