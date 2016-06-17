<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;

class MemberLogin extends Document\Tag\Area\AbstractArea {

    public function action()
    {
        $memberHelper = new \Members\View\Helper\MembersAuthHelper();
        $flashmessenger = new \Zend_Controller_Action_Helper_FlashMessenger();

        $this->view->loginUri = '/plugin/Members/auth/login-from-area';
        $this->view->hideWhenLoggedIn = $this->view->checkbox('hideWhenLoggedIn')->getData();
        $this->view->isLoggedIn = $memberHelper->isLoggedIn();

        $this->view->back = \Members\Model\Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            ? \Members\Model\Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            : \Members\Model\Configuration::getLocalizedPath('routes.profile');

        if ( $this->getParam('back') ) {
            $this->view->back = $this->getParam('back');
        }

        if ( $this->view->href('redirectAfterSuccess')->getElement() ) {
            $this->view->back = $this->view->href('redirectAfterSuccess')->getFullPath();
        }

        $this->view->error = FALSE;
        foreach ( $flashmessenger->getMessages() as $message ) {
            if ( $message['mode'] == 'area' && $message['type'] == 'danger' ) {
                $this->view->error = $this->view->translate($message['text']);
                break;
            }
        }

    }

}