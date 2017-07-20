<?php

namespace MembersBundle\Document\Areabrick\MembersLogin;

use Pimcore\Extension\Document\Areabrick\AbstractTemplateAreabrick;
use Pimcore\Model\Document\Tag\Area\Info;

class MembersLogin extends AbstractTemplateAreabrick
{
    public function action(Info $info)
    {
        $areaBuilder = new View\AreaBuilder('area');

        $areaBuilder->setup()
            ->setEditMode($this->getView()->editmode)
            ->setBackUrl($this->getParam('back'))
            ->setCurrentUrl($this->getView()->getRequest()->getRequestUri())
            ->setRedirectAfterSuccess($this->getView()->href('redirectAfterSuccess')->getElement())
            ->setSnippetAfterLogin($this->getView()->href('showSnippedWhenLoggedIn')->getElement())
            ->setHideAfterLogin($this->getView()->checkbox('hideWhenLoggedIn')->isChecked());

        $params = $areaBuilder->getViewParams();

        foreach($params as $key => $param) {
            $info->getView()->$key = $params;

        }
    }

    public function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_TWIG;
    }

    public function getName()
    {
        return 'Member Login';
    }

    public function getDescription()
    {
        return '';
    }
}