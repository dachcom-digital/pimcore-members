<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Adapter\Sso;

interface SsoIdentityInterface
{
    /**
     * @return int
     */
    public function getId();

    public function getProvider(): ?string;

    /**
     * @param string|null $provider
     *
     * @return $this
     */
    public function setProvider(?string $provider);

    public function getIdentifier(): ?string;

    /**
     * @param string|null $identifier
     *
     * @return $this
     */
    public function setIdentifier(?string $identifier);

    public function getProfileData(): ?string;

    /**
     * @param string|null $profileData
     *
     * @return $this
     */
    public function setProfileData(?string $profileData);

    /**
     * @return string|null|\Pimcore\Model\DataObject\Data\EncryptedField
     */
    public function getAccessToken();

    /**
     * @param string|null|\Pimcore\Model\DataObject\Data\EncryptedField $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken);

    public function getTokenType(): ?string;

    /**
     * @param string|null $tokenType
     *
     * @return $this
     */
    public function setTokenType(?string $tokenType);

    public function getExpiresAt(): ?string;

    /**
     * @param string|null $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(?string $expiresAt);

    /**
     * @return string|null|\Pimcore\Model\DataObject\Data\EncryptedField
     */
    public function getRefreshToken();

    /**
     * @param string|null|\Pimcore\Model\DataObject\Data\EncryptedField $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken($refreshToken);

    public function getScope(): ?string;

    /**
     * @param string|null $scope
     *
     * @return $this
     */
    public function setScope(?string $scope);
}
