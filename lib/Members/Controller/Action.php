<?php
namespace Members\Controller;

use Website\Controller\Action as WebsiteAction;
use Members\Auth;

class Action extends WebsiteAction
{
    /**
     * @var \Zend_Auth
     */
    protected $auth;

    /**
     * @var \Pimcore\Translate\Website
     */
    protected $translate;

    public function preDispatch()
    {
        parent::preDispatch();

        /*
         * Initialize Session namespace for FlashMessenger through Pimcore Session.
         * Because FlashMessenger does not allow custom Zend_Session_Namespace injection,
         * we need to initialize the session in a early state to prevent a session reset in pimcore session handler.
         * Also add this line to your website Action, if you need FlashMessenger there!
         */
        \Pimcore\Tool\Session::get('FlashMessenger');
    }

    public function init()
    {
        parent::init();

        $this->enableLayout();

        $this->translate = $this->initTranslation();
        $this->auth = Auth\Instance::getAuth();

        //allow website path to override templates
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts');
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/layouts');
        $this->view->flashMessages = $this->_helper->flashMessenger->getMessages();
    }
}