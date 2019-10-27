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
     * @return string
     */
    public function getIdentifier();

    /**
     * @return mixed
     */
    public function getProfileData();

    /**
     * @return mixed
     */
    public function getAccessToken();

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken);

    /**
     * @return mixed
     */
    public function getTokenType();

    /**
     * @param string $tokenType
     */
    public function setTokenType($tokenType);

    /**
     * @return mixed
     */
    public function getExpiresAt();

    /**
     * @param string $expiresAt
     */
    public function setExpiresAt($expiresAt);

    /**
     * @return mixed
     */
    public function getRefreshToken();

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken);

    /**
     * @return mixed
     */
    public function getScope();

    /**
     * @param string $scope
     */
    public function setScope($scope);
}