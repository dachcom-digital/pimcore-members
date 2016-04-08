<?php
namespace Members\Controller;

use Website\Controller\Action as WebsiteAction;

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

        $this->view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/scripts');
        $this->view->addScriptPath(PIMCORE_PLUGINS_PATH . '/Members/views/layouts');

        $this->auth = \Zend_Auth::getInstance();
        // TODO provide plugin translations to frontend
        $this->translate = $this->initTranslation();
        $this->view->flashMessages = $this->_helper->flashMessenger->getMessages();
    }
}