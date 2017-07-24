<?php

namespace MembersBundle\Document\Builder;

use MembersBundle\Adapter\User\UserInterface;
use Pimcore\Model\Document;
use Pimcore\Placeholder;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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
     * @var CsrfTokenManagerInterface
     */
    protected $csrfTokenManager;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $logoutUri = NULL;

    /**
     * @var bool
     */
    protected $hideAfterLogin = FALSE;

    /**
     * @var Document
     */
    protected $redirectPage= NULL;

    /**
     * @var Document\Snippet
     */
    protected $successSnippet = NULL;

    /**
     * @var Request
     */
    protected $request = FALSE;

    /**
     * @var bool
     */
    protected $editMode = FALSE;

    /**
     * @var string
     */
    protected $error = NULL;

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
     * AreaBuilder constructor.
     *
     * @param TokenStorage              $tokenStorage
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param IncludeRenderer           $includeRenderer
     * @param UrlGeneratorInterface     $urlGenerator
     */
    public function __construct(
        TokenStorage $tokenStorage,
        CsrfTokenManagerInterface $csrfTokenManager,
        IncludeRenderer $includeRenderer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->includeRenderer = $includeRenderer;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param $sourceType
     *
     * @return $this
     */
    public function setup($sourceType)
    {
        $this->sourceType = $sourceType;
        $this->logoutUri = $this->urlGenerator->generate('members_user_security_logout');

        return $this;
    }

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
    public function setEditMode($isEditMode = FALSE)
    {
        $this->editMode = $isEditMode;

        return $this;
    }

    public function setTemplate($name, $path = '')
    {
        $this->templates[$name] = $path;
    }

    public function getTemplate($name)
    {
        return $this->templates[$name];
    }

    /**
     * @param Document $page
     * Allowed Types: 'page', 'link', 'hardlink'
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
    public function setHideAfterLogin($hide = FALSE)
    {
        if (is_string($hide)) {
            $this->hideAfterLogin = $hide === '1';
        } else if (is_bool($hide)) {
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
        } else if (!$this->tokenStorage->getToken()->getUser() instanceof UserInterface) {

            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;

            // get the error if any (works with forward and redirect -- see below)
            if ($this->request->attributes->has($authErrorKey)) {
                $error = $this->request->attributes->get($authErrorKey);
            } elseif (NULL !== $this->request->getSession() && $this->request->getSession()->has($authErrorKey)) {
                $error = $this->request->getSession()->get($authErrorKey);
                $this->request->getSession()->remove($authErrorKey);
            } else {
                $error = NULL;
            }

            if (!$error instanceof AuthenticationException) {
                $error = NULL; // The value does not come from the security component.
            }

            // last username entered by the user
            $lastUsername = (NULL === $this->request->getSession()) ? '' : $this->request->getSession()->get($lastUsernameKey);
            $csrfToken = $this->csrfTokenManager->getToken('authenticate')->getValue();

            $params = array_merge($params, [
                'last_username' => $lastUsername,
                'error'         => $error,
                'csrf_token'    => $csrfToken,
                'target_path'   => is_null($this->redirectPage) ? $this->request->getRequestUri() : $this->redirectPage->getFullPath(),
                'failure_path'  => $this->request->getRequestUri()
            ]);

            $template = $this->getTemplate('area-login');
        } elseif ($this->tokenStorage->getToken()->getUser() instanceof UserInterface) {

            if ($this->hideAfterLogin === FALSE && !is_null($this->successSnippet)) {

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
            } elseif ($this->hideAfterLogin === FALSE) {
                $template = $this->getTemplate('area-logged-in');
            }
        }

        $params['members_area_template'] = $template;

        return $params;
    }
}