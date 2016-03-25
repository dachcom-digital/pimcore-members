<?php

use Pimcore\Controller\Action\Admin;
use Pimcore\Tool as PimTool;

class Members_Admin_RestrictionController extends Admin
{
    public function getDocumentRestrictionConfigAction()
    {
        $documentId = $this->getParam('docId');

        $this->_helper->json(array(

            'success' => TRUE,
            'docId' => (int) $documentId,
            'isActive' => TRUE,
            'userGroups' => array()

        ));
    }

}