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

namespace MembersBundle\Document\Builder;

use MembersBundle\Adapter\User\UserInterface;
use Pimcore\Model\Document;
use Pimcore\Templating\Renderer\IncludeRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Twig\Environment;

class BrickBuilder
{
    protected string $sourceType = 'area';
    protected ?string $logoutUri = null;
    protected bool $hideAfterLogin = false;
    protected ?Document $redirectPage = null;
    protected ?Document\Snippet $successSnippet = null;
    protected ?Request $request = null;
    protected bool $editMode = false;
    protected ?string $error = null;
    private array $templates = [
        'area-login'             => 'auth/area/login_area',
        'area-logged-in'         => 'auth/area/login_area_logged_in',
        'area-logged-in-snippet' => 'auth/area/login_area_logged_in_snippet',
        'area-not-available'     => 'auth/area/frontend_request'
    ];

    public function __construct(
        protected TokenStorageInterface $tokenStorage,
        protected IncludeRenderer $includeRenderer,
        protected Environment $templateRenderer,
        protected UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function setup(string $sourceType): self
    {
        $this->sourceType = $sourceType;
        $this->logoutUri = $this->urlGenerator->generate('members_user_security_logout');

        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setEditMode(bool $isEditMode = false): self
    {
        $this->editMode = $isEditMode;

        return $this;
    }

    public function setTemplate(string $name, string $path = ''): void
    {
        $this->templates[$name] = $path;
    }

    public function getTemplate(string $name): string
    {
        return $this->templates[$name];
    }

    /**
     * Allowed Types: 'page', 'link', 'hardlink'.
     */
    public function setRedirectAfterSuccess(?Document $page): self
    {
        if ($page instanceof Document) {
            $this->redirectPage = $page;
        }

        return $this;
    }

    public function setSnippetAfterLogin(?Document\Snippet $snippet): self
    {
        if ($snippet instanceof Document\Snippet) {
            $this->successSnippet = $snippet;
        }

        return $this;
    }

    public function setHideAfterLogin(string|bool $hide = false): self
    {
        if (is_string($hide)) {
            $this->hideAfterLogin = $hide === '1';
        } elseif (is_bool($hide)) {
            $this->hideAfterLogin = $hide;
        }

        return $this;
    }

    public function getViewParams(): array
    {
        $template = '';

        $params = [
            'builder_type'            => $this->sourceType,
            'login_uri'               => $this->urlGenerator->generate('members_user_security_login'),
            'logout_uri'              => $this->logoutUri,
            'is_logged_in'            => $this->tokenStorage->getToken()?->getUser() instanceof UserInterface,
            'members_user'            => $this->tokenStorage->getToken()?->getUser(),
            'hide_when_logged_in'     => $this->hideAfterLogin,
            'origin'                  => $this->request->getRequestUri(),
            'error'                   => $this->error,
            'members_snippet_content' => ''
        ];

        if ($this->editMode) {
            //only show backend note
            $template = $this->getTemplate('area-not-available');
        } elseif (!$this->tokenStorage->getToken()?->getUser() instanceof UserInterface) {
            $authErrorKey = SecurityRequestAttributes::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityRequestAttributes::LAST_USERNAME;

            // get the error if any (works with forward and redirect -- see below)
            if ($this->request->attributes->has($authErrorKey)) {
                $error = $this->request->attributes->get($authErrorKey);
            } elseif ($this->request->getSession()->has($authErrorKey)) {
                $error = $this->request->getSession()->get($authErrorKey);
                $this->request->getSession()->remove($authErrorKey);
            } else {
                $error = null;
            }

            if (!$error instanceof AuthenticationException) {
                $error = null; // The value does not come from the security component.
            }

            // last username entered by the user
            $lastUsername = $this->request->getSession()->get($lastUsernameKey, '');

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

                $snippetContent = $this->includeRenderer->render($this->successSnippet, $snippetParams, $this->editMode);

                $params['members_snippet_content'] = $this->templateRenderer->createTemplate($snippetContent)->render($snippetParams);

                $template = $this->getTemplate('area-logged-in-snippet');
            } elseif ($this->hideAfterLogin === false) {
                $template = $this->getTemplate('area-logged-in');
            }
        }

        $params['members_area_template'] = $template;

        return $params;
    }
}
