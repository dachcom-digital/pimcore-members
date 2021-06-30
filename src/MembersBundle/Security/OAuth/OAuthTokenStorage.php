<?php

namespace MembersBundle\Security\OAuth;

use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuthTokenStorage implements OAuthTokenStorageInterface
{
    protected SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
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

    protected function getSessionBag(): NamespacedAttributeBag
    {
        /** @var NamespacedAttributeBag $bag */
        return $this->session->getBag('members_session');
    }
}
