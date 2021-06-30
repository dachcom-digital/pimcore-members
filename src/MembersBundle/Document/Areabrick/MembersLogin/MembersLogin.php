<?php

namespace MembersBundle\Document\Areabrick\MembersLogin;

use MembersBundle\Document\Builder\BrickBuilder;
use MembersBundle\Form\Factory\FactoryInterface;
use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Editable\Area\Info;

class MembersLogin extends AbstractTemplateAreabrick
{
    protected BrickBuilder $brickBuilder;
    protected FactoryInterface $formFactory;

    public function __construct(
        BrickBuilder $brickBuilder,
        FactoryInterface $formFactory
    ) {
        $this->brickBuilder = $brickBuilder;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function action(Info $info)
    {
        /** @var \Pimcore\Model\Document\Editable\Relation $redirectAfterSuccessElement */
        $redirectAfterSuccessElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'redirectAfterSuccess');
        $redirectAfterSuccess = $redirectAfterSuccessElement->getElement();

        /** @var \Pimcore\Model\Document\Editable\Relation $showSnippedWhenLoggedInElement */
        $showSnippedWhenLoggedInElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'showSnippedWhenLoggedIn');
        $showSnippedWhenLoggedIn = $showSnippedWhenLoggedInElement->getElement();

        /** @var \Pimcore\Model\Document\Editable\Checkbox $hideWhenLoggedInElement */
        $hideWhenLoggedInElement = $this->getDocumentEditable($info->getDocument(), 'checkbox', 'hideWhenLoggedIn');
        $hideWhenLoggedIn = $hideWhenLoggedInElement->isChecked();

        $this->brickBuilder->setup('area')
            ->setRequest($info->getRequest())
            ->setEditMode($info->getParam('editmode'))
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

        $form = $this->formFactory->createUnnamedFormWithOptions($formParams);

        $info->setParam('form', $form->createView());
        foreach ($params as $key => $param) {
            $info->setParam($key, $param);
        }

        return null;
    }

    public function hasEditTemplate(): bool
    {
        return true;
    }

    public function getViewTemplate(): string
    {
        return 'MembersBundle:Areas/MembersLogin:view.' . $this->getTemplateSuffix();
    }

    public function getEditTemplate(): string
    {
        return 'MembersBundle:Areas/MembersLogin:edit.' . $this->getTemplateSuffix();
    }

    public function getTemplateSuffix(): string
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    public function getName(): string
    {
        return 'Member Login';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getHtmlTagOpen(Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Info $info): string
    {
        return '';
    }
}
