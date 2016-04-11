<?php

namespace Members\Controller\Plugin;

use Pimcore\Model\Document\Page;
use Members\Tool\Tool;
use Members\Model\Configuration;

class Frontend extends \Zend_Controller_Plugin_Abstract
{
    public function postDispatch(\Zend_Controller_Request_Abstract $request)
    {
        parent::preDispatch($request);

        if ($request->getParam('document') instanceof Page)
        {
            $document = $request->getParam('document');

            $groups = Tool::getDocumentRestrictedGroups( $document );

            $renderer = \Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer');
            $renderer->initView();

            $view = $renderer->view;

            $view->headMeta()->appendName('m:groups', implode(',', $groups), array());

            $this->handleDocumentAuthentication($request->getParam('document'));
        }

    }

    /**
     * @param Page $document
     *
     * @return bool
     */
    private function handleDocumentAuthentication($document)
    {
        //@fixme! bad?
        if (isset($_COOKIE['pimcore_admin_sid']))
        {
            return FALSE;
        }

        //@fixme: does not work in backend? :)
        if( !\Pimcore\Tool::isFrontend() )
        {
            return FALSE;
        }

        //now load restriction, and redirect user to login page, if page is restricted!
        if( Tool::isRestrictedDocument( $document ) )
        {
            $response = $this->getResponse();
            $response->setHeader('Location', Configuration::getLocalizedPath('routes.login'));
            $response->setRawHeader(302);
            $response->sendHeaders();
            exit;
        }

    }
}