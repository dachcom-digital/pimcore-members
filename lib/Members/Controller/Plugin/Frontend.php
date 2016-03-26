<?php

namespace Members\Controller\Plugin;

use Pimcore\Model\Document\Page;
use Members\Model\Restriction;

class Frontend extends \Zend_Controller_Plugin_Abstract
{
    public function preDispatch(\Zend_Controller_Request_Abstract $request)
    {
        parent::preDispatch($request);

        if ($request->getParam('document') instanceof Page)
        {
            $this->handleDocumentAuthentication($request->getParam('document'));
        }
    }

    /**
     * @param Page $document
     */
    private function handleDocumentAuthentication($document)
    {
        //now load restriction, and redirect user to login page, if page is restricted!
    }
}