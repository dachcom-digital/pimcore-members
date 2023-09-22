<?php

namespace MembersBundle\Security\OAuth;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

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

    protected function getSessionBag(): SessionBagInterface
    {
        return $this->getSession()->getBag('members_session');
    }

    private function getSession(): Session
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new \LogicException('Cannot get the session without an active request.');
        }

        return $request->getSession();
    }
}
