<?php

use Pimcore\Model\Object;

use Members\Controller\Action;
use Members\Model\Configuration;
use Members\Tool\Identifier;

class Members_AuthController extends Action
{
    public function loginAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.profile'));
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

            $identifier = new Identifier();

            if ($identifier->setIdentity($identity, $password)->isValid())
            {
                /**
                 * Set the Session Cookie to 7 Days.
                 */
                if( !is_null( $this->_getParam('remember') ) )
                {
                    \Zend_Session::rememberMe(604800);
                }

                if ($this->_getParam('back'))
                {
                    $this->redirect( $this->_getParam('back') );
                }

                $this->redirect( Configuration::getLocalizedPath('routes.profile') );
            }

            switch ($identifier->getCode())
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
        $this->redirect(Configuration::getLocalizedPath('routes.login'));
    }

    public function passwordRequestAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.profile'));
        }

        if ($this->_request->isPost())
        {
            $email = trim($this->_request->getPost('email'));
            if (!\Zend_Validate::is($email, 'EmailAddress'))
            {
                $this->view->error = $this->translate->_('Provide valid email address.');
                return;
            }

            // TODO resend confirmation email if account is not active
            $list = Object\Member::getByEmail($email);

            if (count($list) == 0)
            {
                $this->view->error = $this->translate->_('User with given email not exist.');
                return;
            }

            /** @var \Pimcore\Model\Object\Member $member */
            $member = $list->current();
            $member->requestPasswordReset();
            $this->_helper->flashMessenger([
                'type' => 'success',
                'text' => $this->translate->_('Password reset confirmation was sent to given email address.'),
            ]);

            $this->redirect(Configuration::getLocalizedPath('routes.login'));
        }
    }

    public function passwordResetAction()
    {
        if ($this->_helper->member())
        {
            $this->redirect(Configuration::getLocalizedPath('routes.profile'));
        }

        $hash = trim($this->_getParam('hash'));

        if (empty($hash))
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('Invalid password reset link.'),
            ]);

            $this->redirect(Configuration::getLocalizedPath('routes.login'));

        }

        $list = new Object\Member\Listing();
        $list->setUnpublished(true);
        $list->setCondition('resetHash = ?', $hash);

        if (count($list) == 0)
        {
            $this->_helper->flashMessenger([
                'type' => 'danger',
                'text' => $this->translate->_('Invalid password reset link.')
            ]);

            $this->redirect(Configuration::getLocalizedPath('routes.login'));
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
                    'text' => $this->translate->_('Your password has been successfully changed.')
                ]);

                $this->redirect(Configuration::getLocalizedPath('routes.login'));
            }

            $this->view->errors = $result->getMessages();
        }
    }

}