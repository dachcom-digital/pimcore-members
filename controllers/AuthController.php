<?php

use Members\Auth\Adapter;
use Members\Controller\Action;
use Members\Model\Configuration;
use Pimcore\Model\Object;

class Members_AuthController extends Action
{
    public function loginAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::get('routes.profile'));
        }

        if ($this->_request->isPost())
        {
            $identity = trim($this->_getParam('email'));
            $password = $this->_getParam('password');

            if (empty($identity) || empty($password))
            {
                $this->view->error = $this->translate->_('Wrong email or password');
                return;
            }

            $adapterSettings = array(

                'identityClassname' =>  Configuration::get('auth.adapter.identityClassname'),
                'identityColumn' =>  Configuration::get('auth.adapter.identityColumn'),
                'credentialColumn' =>  Configuration::get('auth.adapter.credentialColumn'),
                'objectPath' =>  Configuration::get('auth.adapter.objectPath')

            );

            $adapter = new Adapter( $adapterSettings );
            $adapter
                ->setIdentity($identity)
                ->setCredential($password);
            $result = $this->auth->authenticate($adapter);

            if ($result->isValid())
            {
                // TODO handle "remember me"
                if ($this->_getParam('back')) {
                    $this->redirect($this->_getParam('back'));
                }
                $this->redirect(Configuration::get('routes')->profile);
            }

            switch ($result->getCode())
            {
                case \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                case \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                    $error = $this->translate->_('Wrong email or password');
                    break;
                default:
                    $error = $this->translate->_('Unexpected error occurred');
                    break;
            }

            $this->view->error = $error;
        }

    }

    public function logoutAction()
    {
        $this->auth->clearIdentity();
        $this->redirect(Configuration::get('routes.login'));
    }

    public function passwordRequestAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::get('routes.profile'));
        }

        if ($this->_request->isPost())
        {
            $email = trim($this->_request->getPost('email'));
            if (!\Zend_Validate::is($email, 'EmailAddress'))
            {
                $this->view->error = $this->translate->_('member_password_request_email_invalid');
                return;
            }

            // TODO resend confirmation email if account is not active
            $list = Object\Member::getByEmail($email);

            if (count($list) == 0) {
                $this->view->error = $this->translate->_('member_password_request_email_not_exist');
                return;
            }

            /** @var \Pimcore\Model\Object\Member $member */
            $member = $list->current();
            $member->requestPasswordReset();
            $this->_helper->flashMessenger([
                'type' => 'success',
                'text' => $this->translate->_('member_password_request_success'),
            ]);

            $this->redirect(Configuration::get('routes.login'));
        }
    }

    public function passwordResetAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::get('routes.profile'));
        }

        $hash = trim($this->_getParam('hash'));

        if (empty($hash))
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('member_password_reset_link_invalid'),
            ]);

            $this->redirect(Configuration::get('routes')->login);

        }

        $list = new Object\Member\Listing();
        $list->setUnpublished(true);
        $list->setCondition('resetHash = ?', $hash);

        if (count($list) == 0)
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('member_password_reset_link_invalid')
            ]);

            $this->redirect(Configuration::get('routes.login'));
        }

        if ($this->_request->isPost())
        {
            $post = $this->_request->getPost();
            /** @var \Pimcore\Model\Object\Member $member */
            $member = $list->current();
            $result = $member->resetPassword($post);
            if ($result->isValid())
            {
                $this->_helper->flashMessenger([
                    'type' => 'success',
                    'text' => $this->translate->_('member_password_reset_success')
                ]);

                $this->redirect(Configuration::get('routes.login'));
            }

            $this->view->errors = $result->getMessages();
        }
    }

}