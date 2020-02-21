<?php

namespace MembersBundle\Document\Builder;

use MembersBundle\Adapter\User\UserInterface;
use Pimcore\Model\Document;
use Pimcore\Placeholder;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;

class BrickBuilder
{
    /**
     * @var string
     */
    protected $sourceType = 'area';

    /**
     * @var IncludeRenderer
     */
    protected $includeRenderer;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $logoutUri = null;

    /**
     * @var bool
     */
    protected $hideAfterLogin = false;

    /**
     * @var Document
     */
    protected $redirectPage = null;

    /**
     * @var Document\Snippet
     */
    protected $successSnippet = null;

    /**
     * @var Request
     */
    protected $request = false;

    /**
     * @var bool
     */
    protected $editMode = false;

    /**
     * @var string
     */
    protected $error = null;

    /**
     * @var array
     */
    private $templates = [
        'area-login'             => 'Auth/Area/login_area',
        'area-logged-in'         => 'Auth/Area/login_area_logged_in',
        'area-logged-in-snippet' => 'Auth/Area/login_area_logged_in_snippet',
        'area-not-available'     => 'Auth/Area/frontend_request'
    ];

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param IncludeRenderer       $includeRenderer
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        IncludeRenderer $includeRenderer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->includeRenderer = $includeRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string $sourceType
     *
     * @return $this
     */
    public function setup($sourceType)
    {
        $this->sourceType = $sourceType;
        $this->logoutUri = $this->urlGenerator->generate('members_user_security_logout');

        return $this;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param bool $isEditMode
     *
     * @return $this
     */
    public function setEditMode($isEditMode = false)
    {
        $this->editMode = $isEditMode;

        return $this;
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function setTemplate($name, $path = '')
    {
        $this->templates[$name] = $path;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getTemplate($name)
    {
        return $this->templates[$name];
    }

    /**
     * Allowed Types: 'page', 'link', 'hardlink'.
     *
     * @param Document $page
     *
     * @return $this
     */
    public function setRedirectAfterSuccess($page)
    {
        if ($page instanceof Document) {
            $this->redirectPage = $page;
        }

        return $this;
    }

    /**
     * @param Document\Snippet $snippet
     *
     * @return $this
     */
    public function setSnippetAfterLogin($snippet)
    {
        if ($snippet instanceof Document\Snippet) {
            $this->successSnippet = $snippet;
        }

        return $this;
    }

    /**
     * @param string|bool $hide
     *
     * @return $this
     */
    public function setHideAfterLogin($hide = false)
    {
        if (is_string($hide)) {
            $this->hideAfterLogin = $hide === '1';
        } elseif (is_bool($hide)) {
            $this->hideAfterLogin = $hide;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getViewParams()
    {
        $template = '';

        $params = [
            'builder_type'            => $this->sourceType,
            'login_uri'               => $this->urlGenerator->generate('members_user_security_login'),
            'logout_uri'              => $this->logoutUri,
            'is_logged_in'            => $this->tokenStorage->getToken()->getUser() instanceof UserInterface,
            'members_user'            => $this->tokenStorage->getToken()->getUser(),
            'hide_when_logged_in'     => $this->hideAfterLogin,
            'origin'                  => $this->request->getRequestUri(),
            'error'                   => $this->error,
            'members_snippet_content' => ''
        ];

        if ($this->editMode) {
            //only show backend note
            $template = $this->getTemplate('area-not-available');
        } elseif (!$this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;

            // get the error if any (works with forward and redirect -- see below)
            if ($this->request->attributes->has($authErrorKey)) {
                $error = $this->request->attributes->get($authErrorKey);
            } elseif (null !== $this->request->getSession() && $this->request->getSession()->has($authErrorKey)) {
                $error = $this->request->getSession()->get($authErrorKey);
                $this->request->getSession()->remove($authErrorKey);
            } else {
                $error = null;
            }

            if (!$error instanceof AuthenticationException) {
                $error = null; // The value does not come from the security component.
            }

            // last username entered by the user
            $lastUsername = (null === $this->request->getSession()) ? '' : $this->request->getSession()->get($lastUsernameKey);

            $params = array_merge($params, [
                'last_username' => $lastUsername,
                'error'         => $error,
                'target_path'   => is_null($this->redirectPage) ? $this->request->getRequestUri() : $this->redirectPage->getFullPath(),
                'failure_path'  => $this->request->getRequestUri()
            ]);

            $template = $this->getTemplate('area-login');
        } elseif ($this->tokenStorage->getToken()->getUser() instanceof UserInterface) {
            if ($this->hideAfterLogin === false && !is_null($this->successSnippet)) {
                $snippetParams = [
                    'user'         => $this->tokenStorage->getToken()->getUser(),
                    'redirect_uri' => is_null($this->redirectPage) ? $this->request->getRequestUri() : $this->redirectPage->getFullPath(),
                    'logout_uri'   => $this->logoutUri,
                    'current_uri'  => $this->request->getRequestUri()
                ];

                $placeholder = new Placeholder();
                $snippetContent = $this->includeRenderer->render($this->successSnippet, $snippetParams, $this->editMode);
                $params['members_snippet_content'] = $placeholder->replacePlaceholders($snippetContent, $snippetParams);

                $template = $this->getTemplate('area-logged-in-snippet');
            } elseif ($this->hideAfterLogin === false) {
                $template = $this->getTemplate('area-logged-in');
            }
        }

        $params['members_area_template'] = $template;

        return $params;
    }
}
