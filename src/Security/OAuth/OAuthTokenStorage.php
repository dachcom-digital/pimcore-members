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

namespace MembersBundle\Security\OAuth;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuthTokenStorage implements OAuthTokenStorageInterface
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse): void
    {
        $this->getSessionBag()->set($this->buildSessionKey('token', $key), $OAuthResponse);
        $this->getSessionBag()->set($this->buildSessionKey('timestamp', $key), time());
    }

    public function loadToken(string $key, int $maxLifetime = 300): ?OAuthResponseInterface
    {
        $timestamp = $this->getSessionBag()->get($this->buildSessionKey('timestamp', $key));
        $token = $this->getSessionBag()->get($this->buildSessionKey('token', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $token;
        }

        return null;
    }

    public function destroyToken(string $key): void
    {
        $this->getSessionBag()->remove($this->buildSessionKey('token', $key));
        $this->getSessionBag()->remove($this->buildSessionKey('timestamp', $key));
    }

    protected function buildSessionKey(string $type, string $key): string
    {
        return sprintf('members.oauth.token.%s.%s', $type, $key);
    }

    protected function getSessionBag(): AttributeBagInterface
    {
        $session = $this->getSession();
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $session->getBag('members_session');

        return $sessionBag;
    }

    private function getSession(): SessionInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new \LogicException('Cannot get the session without an active request.');
        }

        return $request->getSession();
    }
}
