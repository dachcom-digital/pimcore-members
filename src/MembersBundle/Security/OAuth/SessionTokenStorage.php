<?php

namespace MembersBundle\Security\OAuth;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionTokenStorage implements TokenStorageInterface
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
     * @param string                 $key
     * @param OAuthResponseInterface $OAuthResponse
     */
    public function saveToken(string $key, OAuthResponseInterface $OAuthResponse)
    {
        $this->session->set($this->buildSessionKey('token', $key), $OAuthResponse);
        $this->session->set($this->buildSessionKey('timestamp', $key), time());
    }

    /**
     * @param string $key
     * @param int    $maxLifetime
     *
     * @return OAuthResponseInterface|null
     */
    public function loadToken(string $key, int $maxLifetime = 300)
    {
        $timestamp = $this->getAndRemoveValueFromSession($this->buildSessionKey('timestamp', $key));
        $token = $this->getAndRemoveValueFromSession($this->buildSessionKey('token', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $token;
        }
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    protected function getAndRemoveValueFromSession(string $name, $default = null)
    {
        $value = $this->session->get($name, $default);
        $this->session->remove($name);

        return $value;
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
}
