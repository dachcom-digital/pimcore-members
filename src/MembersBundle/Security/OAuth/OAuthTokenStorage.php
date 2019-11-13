<?php

namespace MembersBundle\Security\OAuth;

use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OAuthTokenStorage implements OAuthTokenStorageInterface
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse)
    {
        $this->getSessionBag()->set($this->buildSessionKey('token', $key), $OAuthResponse);
        $this->getSessionBag()->set($this->buildSessionKey('timestamp', $key), time());
    }

    /**
     * {@inheritdoc}
     */
    public function loadToken(string $key, int $maxLifetime = 300)
    {
        $timestamp = $this->getSessionBag()->get($this->buildSessionKey('timestamp', $key));
        $token = $this->getSessionBag()->get($this->buildSessionKey('token', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $token;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroyToken(string $key)
    {
        $this->getSessionBag()->remove($this->buildSessionKey('token', $key));
        $this->getSessionBag()->remove($this->buildSessionKey('timestamp', $key));
    }

    /**
     * @param string $type
     * @param string $key
     *
     * @return string
     */
    protected function buildSessionKey(string $type, string $key): string
    {
        return sprintf('members.oauth.token.%s.%s', $type, $key);
    }

    /**
     * @return NamespacedAttributeBag
     */
    protected function getSessionBag()
    {
        /** @var NamespacedAttributeBag $bag */
        $bag = $this->session->getBag('members_session');

        return $bag;
    }
}
