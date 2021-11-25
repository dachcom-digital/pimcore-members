<?php

namespace MembersBundle\Document\Areabrick\MembersLogin;

use MembersBundle\Document\Builder\BrickBuilder;
use MembersBundle\Form\Factory\FactoryInterface;
use Pimcore\Extension\Document\Areabrick\AbstractAreabrick;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxConfiguration;
use Pimcore\Extension\Document\Areabrick\EditableDialogBoxInterface;
use Pimcore\Model\Document;
use Pimcore\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;

class MembersLogin extends AbstractAreabrick implements EditableDialogBoxInterface
{
    protected BrickBuilder $brickBuilder;
    protected FactoryInterface $formFactory;
    protected Translator $translator;

    public function __construct(
        BrickBuilder $brickBuilder,
        FactoryInterface $formFactory,
        Translator $translator
    ) {
        $this->brickBuilder = $brickBuilder;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
    }

    public function getEditableDialogBoxConfiguration(Document\Editable $area, ?Document\Editable\Area\Info $info): EditableDialogBoxConfiguration
    {
        $editableDialog = new EditableDialogBoxConfiguration();
        $editableDialog->setWidth(600);

        $editableDialog->setItems([
            [
                'type' => 'relation',
                'label' => $this->translator->trans('redirect after successful login', [], 'admin'),
                'name' => 'redirectAfterSuccess',
                'config' => [
                    'types' => ['document'],
                    'subtypes' => [
                        'document' => ['page', 'link', 'hardlink']
                    ]
                ]
            ],
            [
                'type' => 'checkbox',
                'label' => $this->translator->trans('Hide when logged in', [], 'admin'),
                'name' => 'hideWhenLoggedIn',
            ],
            [
                'type' => 'relation',
                'label' => $this->translator->trans('Show this snippet when logged in', [], 'admin'),
                'name' => 'showSnippedWhenLoggedIn',
                'config' => [
                    'types' => ['document'],
                    'subtypes' => [
                        'document' => ['snippet']
                    ]
                ]
            ],
        ]);

        return $editableDialog;
    }

    public function action(Document\Editable\Area\Info $info): ?Response
    {
        /** @var Document\Editable\Relation $redirectAfterSuccessElement */
        $redirectAfterSuccessElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'redirectAfterSuccess');
        $redirectAfterSuccess = $redirectAfterSuccessElement->getElement();

        /** @var Document\Editable\Relation $showSnippedWhenLoggedInElement */
        $showSnippedWhenLoggedInElement = $this->getDocumentEditable($info->getDocument(), 'relation', 'showSnippedWhenLoggedIn');
        $showSnippedWhenLoggedIn = $showSnippedWhenLoggedInElement->getElement();

        /** @var Document\Editable\Checkbox $hideWhenLoggedInElement */
        $hideWhenLoggedInElement = $this->getDocumentEditable($info->getDocument(), 'checkbox', 'hideWhenLoggedIn');
        $hideWhenLoggedIn = $hideWhenLoggedInElement->isChecked();

        $this->brickBuilder->setup('area')
            ->setRequest($info->getRequest())
            ->setEditMode($info->getEditable()->getEditmode())
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

        return new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200);
    }

    public function getTemplate(): string
    {
        return sprintf('@Members/areas/members-login/view.%s', $this->getTemplateSuffix());
    }

    public function getTemplateLocation(): string
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
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

    public function getHtmlTagOpen(Document\Editable\Area\Info $info): string
    {
        return '';
    }

    public function getHtmlTagClose(Document\Editable\Area\Info $info): string
    {
        return '';
    }
}
