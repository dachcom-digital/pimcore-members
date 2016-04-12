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

        //now load restriction and redirect user to login page, if page is restricted!
        $restrictedType = Tool::isRestrictedDocument( $document );

        if( $restrictedType['section'] == Tool::SECTION_ALLOWED )
        {
            return FALSE;
        }

        if(  $restrictedType['state'] == Tool::STATE_LOGGED_IN && $restrictedType['section'] == Tool::SECTION_ALLOWED )
        {
            return FALSE;
        }

        //do not check /members pages, they will check them itself.
        $requestUrl = $this->getRequest()->getRequestUri();
        $nowAllowed = array(
            Configuration::getLocalizedPath('routes.login'),
            Configuration::getLocalizedPath('routes.profile')
        );

        foreach( $nowAllowed as $not)
        {
            if( substr($requestUrl, 0, strlen($not)) == $not)
            {
                return FALSE;
            }
        }

        if( in_array($this->getRequest()->getRequestUri(), $nowAllowed) )
        {
            return FALSE;
        }

        if( $restrictedType['state'] === Tool::STATE_LOGGED_IN && $restrictedType['section'] === Tool::SECTION_NOT_ALLOWED)
        {
            $url = Configuration::getLocalizedPath('routes.profile');
        }
        else
        {
            $url = sprintf('%s?back=%s',
                Configuration::getLocalizedPath('routes.login'),
                urlencode( $this->getRequest()->getRequestUri() )
            );
        }

        $response = $this->getResponse();
        $response->setHeader('Location', $url);
        $response->setRawHeader(302);
        $response->sendHeaders();
        exit;

    }
}