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

    public function init()
    {
        parent::init();

        $this->enableLayout();

        //allow website to use own scripts
        $this->view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/scripts');
        $this->view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/layouts');
        $this->view->addScriptPath(PIMCORE_WEBSITE_PATH . '/views/scripts/members');

        $this->translate = $this->initTranslation();

        $this->auth = Auth\Instance::getAuth();
        $this->view->flashMessages = $this->_helper->flashMessenger->getMessages();
    }
}