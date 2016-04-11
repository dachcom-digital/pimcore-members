<?php

use Pimcore\Model\Object;
use Members\Controller\Action;
use Members\Model\Configuration;

class Members_ProfileController extends Action
{
    public function defaultAction()
    {
        $this->_helper->member->requireAuth();
        $this->view->member = $this->auth->getIdentity();
    }

    public function registerAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.profile'));
        }

        if ($this->_request->isPost())
        {
            $post = $this->_request->getPost();
            $member = new Object\Member();
            $result = $member->register($post);

            if ($result->isValid())
            {
                $translationKey = 'member_register_success';

                if (Configuration::get('actions.postRegister'))
                {
                    $translationKey .= '_' . Configuration::get('actions.postRegister');
                }

                $this->_helper->flashMessenger([
                    'type' => 'success',
                    'text' => $this->translate->_($translationKey)
                ]);

                $this->redirect(Configuration::getLocalizedPath('routes.login'));
            }

            $this->view->assign(array_merge($post, $result->getEscaped()));
            $this->view->errors = $result->getMessages();
        }

    }

    public function confirmAction()
    {
        $hash = trim($this->_getParam('hash'));

        if (empty($hash))
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('Invalid confirmation link.')
            ]);

            $this->redirect(Configuration::getLocalizedPath('routes.login'));
        }

        $list = new Object\Member\Listing();
        $list->setUnpublished(true);
        $list->setCondition('confirmHash = ?', $hash);

        if (count($list) == 0)
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('Invalid confirmation link.')
            ]);

            $this->redirect(Configuration::getLocalizedPath('routes.login'));
        }

        $member = $list->current();
        $member->confirm();

        $this->_helper->flashMessenger([
            'type' => 'success',
            'text' => $this->translate->_('Your account is now active, you can login using email and password.')
        ]);

        $this->redirect(Configuration::getLocalizedPath('routes.login'));
    }
}