<?php

namespace MembersBundle\Document\Areabrick\MembersLogin;

use MembersBundle\Document\Builder\BrickBuilder;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;

class MembersLogin extends AbstractTemplateAreabrick
{
    /**
     * @var BrickBuilder
     */
    protected $brickBuilder;

    /**
     * MembersLogin constructor.
     *
     * @param BrickBuilder $brickBuilder
     */
    public function __construct(BrickBuilder $brickBuilder)
    {
        $this->brickBuilder = $brickBuilder;
    }

    /**
     * @param Info $info
     */
    public function action(Info $info)
    {
        $view = $info->getView();

        $redirectAfterSuccess = $this->getDocumentTag($info->getDocument(), 'href', 'redirectAfterSuccess')->getElement();
        $showSnippedWhenLoggedIn = $this->getDocumentTag($info->getDocument(), 'href', 'showSnippedWhenLoggedIn')->getElement();
        $hideWhenLoggedIn = $this->getDocumentTag($info->getDocument(), 'checkbox', 'hideWhenLoggedIn')->isChecked();

        $this->brickBuilder->setup('area')
            ->setRequest($info->getRequest())
            ->setEditMode($view->get('editmode'))
            ->setRedirectAfterSuccess($redirectAfterSuccess)
            ->setSnippetAfterLogin($showSnippedWhenLoggedIn)
            ->setHideAfterLogin($hideWhenLoggedIn);

        foreach ($this->brickBuilder->getViewParams() as $key => $param) {
            $view->{$key} = $param;
        }
    }

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return TRUE;
    }

    /**
     * @return string
     */
    public function getViewTemplate()
    {
        return 'MembersBundle:Areas/MembersLogin:view.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getEditTemplate()
    {
        return 'MembersBundle:Areas/MembersLogin:edit.' . $this->getTemplateSuffix();
    }

    /**
     * @return string
     */
    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Member Login';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagOpen(Info $info)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHtmlTagClose(Info $info)
    {
        return '';
    }
}