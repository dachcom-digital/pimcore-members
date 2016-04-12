<?php

use Pimcore\Controller\Action\Admin;
use Members\Model\Restriction;

class Members_Admin_RestrictionController extends Admin
{
    public function getDocumentRestrictionConfigAction()
    {
        $documentId = $this->getParam('docId');
        $cType = $this->getParam('cType');

        $restriction = NULL;

        $isActive = FALSE;
        $isInheritable = FALSE;
        $userGroups = array();

        try
        {
            $restriction = Restriction::getByTargetId($documentId, $cType);
        }
        catch(\Exception $e)
        {
        }

        if( !is_null( $restriction ) )
        {
            $isActive = TRUE;
            $isInheritable = $restriction->getInheritable();
            $userGroups = $restriction->getRelatedGroups();
            $cType = $restriction->getCtype();
        }

        $this->_helper->json(
            array(

                'success'       => TRUE,
                'docId'         => (int) $documentId,
                'cType'         => $cType,
                'isActive'      => $isActive,
                'isInheritable' => $isInheritable,
                'userGroups'    => $userGroups

            )
        );

    }

    public function setDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int) $data['docId'];
        $settings = $data['settings'];
        $cType = $data['cType']; //object|page

        $membersDocumentRestrict = $settings['membersDocumentRestrict'];
        $membersDocumentInheritable = $settings['membersDocumentInheritable'];
        $membersDocumentUserGroups = $settings['membersDocumentUserGroups'];

        try
        {
            $restriction = Restriction::getByTargetId( $docId, $cType );

        }
        catch(\Exception $e)
        {
            $restriction = new Restriction();
            $restriction->setTargetId( $docId );
            $restriction->setCtype( $cType );
        }

        //restriction has been disabled! remove everything!
        if( $membersDocumentRestrict === FALSE )
        {
            $restriction->delete();
        }
        else
        {
            $restriction->setCtype( $cType );
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

    public function deleteDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int) $data['docId'];
        $cType = $data['cType']; //object|page

        $restriction = FALSE;

        try
        {
            $restriction = Restriction::getByTargetId( $docId, $cType );

        }
        catch(\Exception $e)
        {

        }

        //restriction has been disabled! remove everything!
        if( $restriction !== FALSE )
        {
            $restriction->delete();
        }

        $this->_helper->json(
            array(
                'success' => TRUE,
            )
        );

    }

}