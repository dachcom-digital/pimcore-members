<?php

namespace Pimcore\Model\Document\Tag\Area;

use Pimcore\Model\Document;
use Members\Model\Configuration;
use Members\View\Helper;

class MemberLogin extends Document\Tag\Area\AbstractArea
{
    /**
     *
     */
    public function action()
    {
        $memberHelper = new Helper\MembersAuthHelper();
        $flashMessenger = new \Zend_Controller_Action_Helper_FlashMessenger();

        $backUri = Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            ? Configuration::getLocalizedPath('routes.login.redirectAfterSuccess')
            : Configuration::getLocalizedPath('routes.profile');

        if ($this->getParam('back')) {
            $backUri = $this->getParam('back');
        }

        if ($this->view->href('redirectAfterSuccess')->getElement()) {
            $backUri = $this->view->href('redirectAfterSuccess')->getFullPath();
        }

        $error = FALSE;
        foreach ($flashMessenger->getMessages() as $message) {
            if ($message['mode'] == 'area' && $message['type'] == 'danger') {
                $error = $this->view->translate($message['text']);
                break;
            }
        }

        $this->view->assign([

            'areaMode'         => TRUE,
            'loginUri'         => '/plugin/Members/auth/login-from-area',
            'logoutUri'        => Configuration::getLocalizedPath('routes.logout'),
            'isLoggedIn'       => $memberHelper->isLoggedIn(),
            'membersUser'      => $memberHelper->getUser(),
            'hideWhenLoggedIn' => $this->view->checkbox('hideWhenLoggedIn')->getData(),
            'origin'           => $this->view->getRequest()->getRequestUri(),
            'back'             => $backUri,
            'error'            => $error

        ]);
    }

}