<?php

namespace MembersBundle\Security;

use MembersBundle\Adapter\User\AbstractUser;
use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Security\Encoder\Factory\UserAwareEncoderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class LuceneSearchAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserAwareEncoderFactory
     */
    protected $userAwareEncoderFactory;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * LuceneSearchAuthenticator constructor.
     *
     * @param UserAwareEncoderFactory $userAwareEncoderFactory
     * @param UserManagerInterface $userManager
     */
    public function __construct(
        UserAwareEncoderFactory $userAwareEncoderFactory,
        UserManagerInterface $userManager
    ) {
        $this->userAwareEncoderFactory = $userAwareEncoderFactory;
        $this->userManager = $userManager;
    }

    /**
     * @param Request $request
     *
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-LUCENE-SEARCH-AUTHORIZATION')) {
            return NULL;
        }

        $tokenParts = explode(' ', $token);
        if (count($tokenParts) == 2 && $tokenParts[0] === 'Basic') {
            try {
                $decoded = base64_decode($tokenParts[1]);
                $usernameAndPassword = explode(':', $decoded);
                if (count($usernameAndPassword) == 2) {
                    return [
                        'username' => $usernameAndPassword[0],
                        'password' => $usernameAndPassword[1]
                    ];
                }
            } catch (\Exception $e) {
                return NULL;
            }
        }
    }

    /**
     * @param mixed                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return AbstractUser
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->userManager->findUserByUsername($credentials['username']);

        if (!$user instanceof AbstractUser) {
            return NULL;
        }

        return $user;
    }

    /**
     * @param mixed         $credentials
     * @param UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->userAwareEncoderFactory->getEncoder($user);
        return $encoder->isPasswordValid($credentials['password'], $credentials['password'], '');
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     *
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $request->attributes->set('user', $token->getUser());
        return null;
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param Request                      $request
     * @param AuthenticationException|NULL $authException
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = NULL)
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return FALSE;
    }
}