<?php

namespace MembersBundle\Adapter\Sso;

interface SsoIdentityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getProvider();

    /**
     * @param string $provider
     */
    public function setProvider($provider);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return string
     */
    public function getProfileData();

    /**
     * @param string $profileData
     */
    public function setProfileData($profileData);

    /**
     * @return string
     */
    public function getAccessToken();

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken);

    /**
     * @return string
     */
    public function getTokenType();

    /**
     * @param string $tokenType
     */
    public function setTokenType($tokenType);

    /**
     * @return string
     */
    public function getExpiresAt();

    /**
     * @param string $expiresAt
     */
    public function setExpiresAt($expiresAt);

    /**
     * @return string
     */
    public function getRefreshToken();

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken);

    /**
     * @return string
     */
    public function getScope();

    /**
     * @param string $scope
     */
    public function setScope($scope);
}