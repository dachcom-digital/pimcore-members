<?php

namespace MembersBundle\Security;

use MembersBundle\Manager\UserManagerInterface;
use MembersBundle\Adapter\User\UserInterface as MembersUserInterface;
use MembersBundle\Security\Encoder\Factory\UserAwareEncoderFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;

class LuceneSearchAuthenticator extends AbstractGuardAuthenticator implements AuthenticatorInterface
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
     * @param UserAwareEncoderFactory $userAwareEncoderFactory
     * @param UserManagerInterface    $userManager
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
     * @return array|bool|string
     */
    public function supports(Request $request)
    {
        return $request->headers->get('X-LUCENE-SEARCH-AUTHORIZATION') !== null;
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $token = $request->headers->get('X-LUCENE-SEARCH-AUTHORIZATION');

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
                return null;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->userManager->findUserByUsername($credentials['username']);
        if (!$user instanceof MembersUserInterface) {
            return null;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->userAwareEncoderFactory->getEncoder($user);

        return $encoder->isPasswordValid($credentials['password'], $credentials['password'], '');
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $request->attributes->set('user', $token->getUser());

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
