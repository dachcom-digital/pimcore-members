<?php

use Pimcore\Controller\Action\Admin;
use Members\Model\Restriction;

class Members_Admin_RestrictionController extends Admin
{
    public function getDocumentRestrictionConfigAction()
    {
        $documentId = $this->getParam('docId');
        $restriction = NULL;

        $isActive = FALSE;
        $isInheritable = FALSE;
        $userGroups = array();

        try
        {
            $restriction = Restriction::getByTargetId($documentId);
        }
        catch(\Exception $e)
        {
        }

        if( !is_null( $restriction ) )
        {
            $isActive = TRUE;
            $isInheritable = $restriction->getInheritable();
            $userGroups = $restriction->getRelatedGroups();
        }

        $this->_helper->json(
            array(

                'success' => TRUE,
                'docId' => (int) $documentId,
                'isActive' => $isActive,
                'isInheritable' => $isInheritable,
                'userGroups' => $userGroups

            )
        );

    }

    public function setDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int) $data['docId'];
        $settings = $data['settings'];

        $membersDocumentRestrict = $settings['membersDocumentRestrict'];
        $membersDocumentInheritable = $settings['membersDocumentInheritable'];
        $membersDocumentUserGroups = $settings['membersDocumentUserGroups'];

        try
        {
            $restriction = Restriction::getByTargetId( $docId );

        }
        catch(\Exception $e)
        {
            $restriction = new Restriction();
            $restriction->setTargetId( $docId );
        }

        //restriction has been disabled! remove everything!
        if( $membersDocumentRestrict === FALSE )
        {
            $restriction->delete();
        }
        else
        {
            $restriction->setInheritable( $membersDocumentInheritable );
            $restriction->setRelatedGroups( $membersDocumentUserGroups );
            $restriction->save();
        }

        $this->_helper->json(
            array(

                'success' => TRUE,
                'docId' => (int) $settings['docId'],
                'isActive' => TRUE,
                'userGroups' => array()

            )
        );

    }

}