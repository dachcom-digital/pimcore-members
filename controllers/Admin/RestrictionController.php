<?php

use Pimcore\Controller\Action\Admin;
use Pimcore\Model\Object\MemberRole;

use Members\Model\Configuration;
use Members\Model\Restriction;

class Members_Admin_RestrictionController extends Admin
{
    /**
     *
     */
    public function getGlobalSettingsAction()
    {
        $config = new Configuration\Listing();

        $valueArray = [];
        foreach ($config->getConfigurations() as $c) {
            $valueArray[$c->getKey()] = $c->getData();
        }

        $this->_helper->json(
            [
                'settings' => $valueArray,
            ]
        );
    }

    /**
     *
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
     *
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

        $this->_helper->json(
            [

                'success'     => TRUE,
                'docId'       => (int)$documentId,
                'cType'       => $cType,
                'isActive'    => $isActive,
                'isInherited' => $isInherited,
                'inherit'     => $inherit,
                'userGroups'  => $userGroups

            ]
        );
    }

    /**
     *
     */
    public function setDocumentRestrictionConfigAction()
    {
        $data = \Zend_Json::decode($this->getParam('data'));

        $docId = (int)$data['docId'];
        $settings = $data['settings'];
        $cType = $data['cType']; //object|page

        //doc / object is inherited, no data given, do nothing!
        if (!isset($settings['membersDocumentRestrict'])) {
            $this->_helper->json(['success' => TRUE]);
        }

        $membersDocumentRestrict = $settings['membersDocumentRestrict'];
        $membersDocumentInheritable = $settings['membersDocumentInheritable'];
        $membersDocumentUserGroups = $settings['membersDocumentUserGroups'];

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
            $restriction = new Restriction();
            $restriction->setTargetId($docId);
            $restriction->setCtype($cType);
        }

        //restriction has been disabled! remove everything!
        if ($membersDocumentRestrict === FALSE) {
            $restriction->delete();
        } else {
            $restriction->setCtype($cType);
            $restriction->setInherit($membersDocumentInheritable);
            //restriction has been explicitly saved, its not inherited anymore!
            $restriction->setIsInherited(FALSE);
            $restriction->setRelatedGroups($membersDocumentUserGroups);
            $restriction->save();
        }

        //get all child elements and store them in members table!
        $type = 'document';
        if ($cType == 'object') {
            $type = 'object';
        }

        $obj = \Pimcore\Model\Element\Service::getElementById($type, $docId);

        if ($obj instanceof \Pimcore\Model\Object\AbstractObject) {
            $list = new \Pimcore\Model\Object\Listing();
            $list->setCondition("o_type = ? AND o_path LIKE ?", ['object', $obj->getFullPath() . '/%']);
        } else if ($obj instanceof \Pimcore\Model\Document) {
            $list = new \Pimcore\Model\Document\Listing();
            $list->setCondition("type = ? AND path LIKE ?", ['page', $obj->getFullPath() . '/%']);
        }

        $list->setLimit(100000);
        $childs = $list->load();

        $excludePaths = [];

        if (!empty($childs)) {
            foreach ($childs as $child) {
                $isNew = FALSE;

                foreach ($excludePaths as $path) {
                    if (substr($child->getFullPath(), 0, strlen($path)) !== FALSE) {
                        continue;
                    }
                }

                try {
                    $restriction = Restriction::getByTargetId($child->getId(), $obj->getType());
                } catch (\Exception $e) {
                    $restriction = new Restriction();
                    $restriction->setTargetId($child->getId());
                    $restriction->setCtype($cType);
                    $isNew = TRUE;
                }

                if ($isNew == FALSE && $restriction->isInherited() === FALSE) {
                    $excludePaths[] = $child->getFullPath();
                    continue;
                }

                $restriction->setCtype($cType);
                $restriction->setRelatedGroups($membersDocumentUserGroups);
                $restriction->save();

                if ($membersDocumentInheritable === TRUE) {
                    $restriction->setIsInherited(TRUE);
                    $restriction->save();
                } else {
                    if (!$restriction->getInherit()) {
                        $restriction->delete();
                    }
                }
            }
        }

        //clear cache!
        \Pimcore\Cache::clearTag('members');

        $this->_helper->json(
            [

                'success'    => TRUE,
                'docId'      => (int)$settings['docId'],
                'isActive'   => TRUE,
                'userGroups' => $restriction->getRelatedGroups()

            ]
        );
    }

    /**
     *
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

        $this->_helper->json(
            [
                'success' => TRUE,
            ]
        );
    }

}