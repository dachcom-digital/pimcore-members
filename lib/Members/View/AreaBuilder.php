<?php

namespace Members\View;

use Members\Model\Configuration;
use Members\Tool\Identifier;
use Pimcore\Model\Document;
use Pimcore\Placeholder;

class AreaBuilder
{
    /**
     * @var string
     */
    protected $sourceType = 'area';

    /**
     * @var string
     */
    protected $backUri = NULL;

    /**
     * @var string
     */
    protected $logoutUri = NULL;

    /**
     * @var bool
     */
    protected $hideAfterLogin = FALSE;

    /**
     * @var int
     */
    protected $redirectPageId = NULL;

    /**
     * @var Document\Snippet
     */
    protected $successSnippet = NULL;

    /**
     * @var bool
     */
    protected $editMode = FALSE;

    /**
     * @var null
     */
    protected $currentUrl = NULL;

    /**
     * @var Identifier
     */
    protected $identifier = NULL;

    /**
     * @var \Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $flashMessenger = NULL;

    /**
     * @var string
     */
    protected $error = NULL;

    /**
     * @var array
     */
    private $templates = [
        'area-login'             => 'members/auth/login-area.php',
        'area-logged-in'         => 'members/auth/login-area-logged-in.php',
        'area-logged-in-snippet' => 'members/auth/login-area-logged-in-snippet.php'
    ];

    /**
     * AreaBuilder constructor.
     *
     * @param $sourceType
     */
    public function __construct($sourceType)
    {
        $this->sourceType = $sourceType;
        $this->identifier = new Identifier();
        $this->flashMessenger = new \Zend_Controller_Action_Helper_FlashMessenger();
    }

    /**
     * @return $this
     */
    public function setup()
    {
        $this->backUri = Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            ? Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            : Configuration::getLocalizedPath('routes.profile');

        $this->logoutUri = Configuration::getLocalizedPath('routes.logout');

        foreach ($this->flashMessenger->getMessages() as $message) {
            if ($message['mode'] == 'area' && $message['type'] === 'danger') {
                $this->error = $this->view->translate($message['text']);
                break;
            }
        }

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
     * @param string $backUri
     *
     * @return $this
     */
    public function setBackUrl($backUri)
    {
        if (!empty($backUri)) {
            $this->backUri = $backUri;
        }

        return $this;
    }

    /**
     * @param string $currentUrl
     *
     * @return $this
     */
    public function setCurrentUrl($currentUrl)
    {
        if (!empty($currentUrl)) {
            $this->currentUrl = $currentUrl;
        }

        return $this;
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
            $this->redirectPageId = $page->getId();
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

            'areaMode'              => TRUE,
            'builderType'           => $this->sourceType,
            'loginUri'              => '/plugin/Members/auth/login-from-area',
            'isLoggedIn'            => $this->identifier->hasIdentity(),
            'membersUser'           => $this->identifier->getIdentity(),
            'hideWhenLoggedIn'      => $this->hideAfterLogin,
            'logoutUri'             => $this->logoutUri,
            'origin'                => $this->currentUrl,
            'back'                  => is_null($this->redirectPageId) ? $this->backUri : $this->redirectPageId,
            'error'                 => $this->error,
            'membersSnippetContent' => ''

        ];

        if ($this->editMode || $this->identifier->hasIdentity() === FALSE) {
            $template = $this->getTemplate('area-login');
        } elseif ($this->identifier->hasIdentity()) {

            if ($this->hideAfterLogin === FALSE && !is_null($this->successSnippet)) {

                /** @var \Pimcore\Controller\Action\Helper\ViewRenderer $viewHelper */
                $viewHelper = \Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
                if ($viewHelper) {

                    if ($viewHelper->view === NULL) {
                        $viewHelper->initView(PIMCORE_WEBSITE_PATH . '/views');
                    }

                    /** @var \Pimcore\View $view */
                    $view = $viewHelper->view;

                    $snippetContent = $view->inc($this->successSnippet->getFullPath());
                    $snippetParams = [
                        'user'        => $this->identifier->getIdentity(),
                        'redirectUri' => is_null($this->redirectPageId) ? $this->backUri : $this->redirectPageId,
                        'logoutUri'   => $this->logoutUri,
                        'currentUri'  => $this->currentUrl
                    ];

                    $placeholder = new Placeholder();
                    $params['membersSnippetContent'] = $placeholder->replacePlaceholders($snippetContent, $snippetParams);
                }

                $template = $this->getTemplate('area-logged-in-snippet');
            } elseif ($this->hideAfterLogin === FALSE) {
                $template = $this->getTemplate('area-logged-in');
            }
        }

        $params['membersAreaTemplate'] = $template;

        return $params;
    }
}