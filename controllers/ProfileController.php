<?php

use Pimcore\Model\Object;
use Members\Controller\Action;
use Members\Model\Configuration;

class Members_ProfileController extends Action
{
    public function defaultAction()
    {
        if( !$this->view->editmode)
        {
            $this->_helper->member->requireAuth();
            $this->view->member = $this->auth->getIdentity();
        }
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
                switch(Configuration::get('actions.postRegister'))
                {
                    case FALSE:
                        $message = 'You account was created successfully and must be activated by site stuff.';
                        break;
                    case 'activate':
                        $message = 'You account was created successfully. You can login with your email and password.';
                        break;
                    case 'confirm':
                        $message = 'You account was created successfully. Check your email and confirm your account by clicking activation link.';
                        break;
                }

                $this->_helper->flashMessenger([
                    'type' => 'success',
                    'text' => $this->translate->_($message)
                ]);

                $this->redirect(Configuration::getLocalizedPath('routes.login'));
            }

            $this->view->assign(array_merge($post, $result->getEscaped()));
            $this->view->errors = $result->getMessages();
        }

    }

    public function updateAction()
    {
        if (!$this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.login'));
        }

        $member = $this->auth->getIdentity();

        if ($this->_request->isPost())
        {
            $post = $this->_request->getPost();
            $result = $member->updateProfile($post);

            if ($result->isValid())
            {
                $message = 'Your profile has been successfully updated.';
                $this->_helper->flashMessenger([
                    'type' => 'success',
                    'text' => $this->translate->_($message)
                ]);

                $this->redirect(Configuration::getLocalizedPath('routes.profile.update'));
            }

            $this->view->assign(array_merge($post, $result->getEscaped()));
            $this->view->errors = $result->getMessages();
        }

        $this->view->member = $member;
        $this->view->isPost = $member;
    }

    public function passwordChangeAction()
    {
        if (!$this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.login'));
        }

        if ($this->_request->isPost())
        {
            $post = $this->_request->getPost();
            /** @var \Pimcore\Model\Object\Member $member */
            $member = $this->_helper->member();
            $result = $member->changePassword($post);
            if ($result->isValid())
            {
                $this->_helper->flashMessenger([
                    'type' => 'success',
                    'text' => $this->translate->_('Your password has been successfully changed. Please login again.')
                ]);

                $this->auth->clearIdentity();
                $this->redirect(Configuration::getLocalizedPath('routes.login'));
            }

            $this->view->errors = $result->getMessages();
        }

        $this->view->member = $this->auth->getIdentity();
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