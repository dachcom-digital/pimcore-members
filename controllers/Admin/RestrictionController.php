<?php

use Pimcore\Controller\Action\Admin;
use Pimcore\Model\Object\MemberRole;

use Members\Model\Configuration;
use Members\Model\Restriction;

class Members_Admin_RestrictionController extends Admin
{
    /**
     * @return void
     */
    public function getGlobalSettingsAction()
    {
        $config = new Configuration\Listing();

        $valueArray = [];
        foreach ($config->getConfigurations() as $c) {
            $valueArray[$c->getKey()] = $c->getData();
        }

        $this->_helper->json(['settings' => $valueArray]);
    }

    /**
     * @return void
     */
    public function getRolesAction()
    {
        $list = new MemberRole\Listing();
        $list->load();

        $roles = [];

        if (is_array($list->getItems(0, 0))) {
            foreach ($list->getItems(0, 0) as $role) {
                $data = [
                    'id'          => $role->getId(),
                    'text'        => $role->getRoleName(),
                    'elementType' => 'role',
                    'qtipCfg'     => [
                        'title' => 'ID: ' . $role->getId()
                    ]
                ];

                $data['leaf'] = TRUE;
                $data['iconCls'] = 'pimcore_icon_roles';
                $data['allowChildren'] = FALSE;

                $roles[] = $data;
            }
        }

        $this->_helper->json($roles);
    }

    /**
     * @return void
     */
    public function getDocumentRestrictionConfigAction()
    {
        $documentId = $this->getParam('docId');
        $cType = $this->getParam('cType');

        $restriction = NULL;

        $isActive = FALSE;
        $isInherited = FALSE;
        $inherit = FALSE;
        $userGroups = [];

        try {
            $restriction = Restriction::getByTargetId($documentId, $cType);
        } catch (\Exception $e) {
        }

        if (!is_null($restriction)) {
            $isActive = TRUE;
            $isInherited = $restriction->isInherited();
            $inherit = $restriction->getInherit();
            $userGroups = $restriction->getRelatedGroups();
            $cType = $restriction->getCtype();
        }

        $this->_helper->json([
            'success'     => TRUE,
            'docId'       => (int)$documentId,
            'cType'       => $cType,
            'isActive'    => $isActive,
            'isInherited' => $isInherited,
            'inherit'     => $inherit,
            'userGroups'  => $userGroups
        ]);
    }

    /**
     * @return void
     */
    public function setDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int)$data['docId'];
        $settings = $data['settings'];
        $cType = $data['cType']; //object|page|asset

        //get all child elements and store them in members table!
        $type = 'document';
        if ($cType == 'object') {
            $type = 'object';
        } else if ($cType == 'asset') {
            $type = 'asset';
        }

        $obj = \Pimcore\Model\Element\Service::getElementById($type, $docId);

        $restriction = NULL;
        $hasRestriction = TRUE;
        $active = FALSE;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
            $hasRestriction = FALSE;
        }

        //remove restriction since they has been disabled.
        if (!isset($settings['membersDocumentRestrict'])) {

            if ($hasRestriction === TRUE) {
                $restriction->delete();
            }

        //update or set restriction
        } else {

            $active = TRUE;

            $membersDocumentInheritable = $settings['membersDocumentInheritable'];
            $membersDocumentUserGroups = explode(',', $settings['membersDocumentUserGroups']);

            if ($hasRestriction === FALSE) {
                $restriction = new Restriction();
                $restriction->setTargetId($docId);
                $restriction->setCtype($cType);
            }


            $restriction->setInherit($membersDocumentInheritable);
            $restriction->setIsInherited(FALSE);
            $restriction->setRelatedGroups($membersDocumentUserGroups);
            $restriction->save();
        }

        \Members\RestrictionService::checkRestrictionContext($obj, $cType);

        //clear cache!
        \Pimcore\Cache::clearTag('members');

        $this->_helper->json([
            'success'    => TRUE,
            'isActive'   => $active,
            'docId'      => (int)$settings['docId'],
            'userGroups' => $active ? $restriction->getRelatedGroups() : []
        ]);
    }

    /**
     * @return void
     */
    public function deleteDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int)$data['docId'];
        $cType = $data['cType']; //object|page

        $restriction = FALSE;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        //restriction has been disabled! remove everything!
        if ($restriction !== FALSE) {
            $restriction->delete();
        }

        $this->_helper->json(['success' => TRUE]);
    }

    /**
     * @return void
     */
    public function getNextParentRestrictionAction()
    {
        $elementId = $this->getParam('docId');
        $cType = $this->getParam('cType');
        $closestInheritanceParent = \Members\RestrictionService::findClosestInheritanceParent($elementId, $cType);

        $this->_helper->json([
            'success' => TRUE,
            'key'     => $closestInheritanceParent['key'],
            'path'    => $closestInheritanceParent['path']
        ]);
    }
}