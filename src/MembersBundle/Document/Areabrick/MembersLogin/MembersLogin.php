<?php

namespace MembersBundle\Document\Areabrick\MembersLogin;

use MembersBundle\Document\Builder\BrickBuilder;
use MembersBundle\Form\Factory\FactoryInterface;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;

class MembersLogin extends AbstractTemplateAreabrick
{
    /**
     * @var BrickBuilder
     */
    protected $brickBuilder;

    /**
     * @var FactoryInterface
     */
    protected $formFactory;

    /**
     * MembersLogin constructor.
     *
     * @param BrickBuilder     $brickBuilder
     * @param FactoryInterface $formFactory
     */
    public function __construct(
        BrickBuilder $brickBuilder,
        FactoryInterface $formFactory
    ) {
        $this->brickBuilder = $brickBuilder;
        $this->formFactory = $formFactory;
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

        $formParams = [];
        $params = $this->brickBuilder->getViewParams();

        if (isset($params['target_path'])) {
            $formParams['_target_path'] = $params['target_path'];
        }

        if (isset($params['last_username'])) {
            $formParams['last_username'] = $params['last_username'];
        }

        if (isset($params['failure_path'])) {
            $formParams['_failure_path'] = $params['failure_path'];
        }

        /** @var $formFactory \MembersBundle\Form\Factory\FactoryInterface */
        $form = $this->formFactory->createUnnamedForm($formParams);

        $view->getParameters()->set('form', $form->createView());
        foreach ($params as $key => $param) {
            $view->getParameters()->set($key, $param);
        }
    }

    /**
     * @return bool
     */
    public function hasEditTemplate()
    {
        return true;
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