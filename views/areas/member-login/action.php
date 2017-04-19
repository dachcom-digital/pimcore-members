<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;
use Members\View;

class MemberLogin extends Document\Tag\Area\AbstractArea
{
    /**
     *
     */
    public function action()
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
        $this->getView()->assign($params);
    }

}