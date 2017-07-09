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
        $cType = $data['cType']; //object|page|asset

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

        $removeChildRestrictions = FALSE;

        //restriction has been disabled! remove everything!
        if ($membersDocumentRestrict === FALSE) {

            //check if element inherits. if so, mark subpath restrictions as deleted.
            if($restriction->getInherit() === TRUE) {
                $removeChildRestrictions = TRUE;
            }

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
        } else if ($cType == 'asset') {
            $type = 'asset';
        }

        $obj = \Pimcore\Model\Element\Service::getElementById($type, $docId);

        if ($obj instanceof \Pimcore\Model\Object\AbstractObject) {
            $list = new \Pimcore\Model\Object\Listing();
            $list->setCondition("o_type = ? AND o_path LIKE ?", ['object', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(o_path) ASC', false);
        } else if ($obj instanceof \Pimcore\Model\Document) {
            $list = new \Pimcore\Model\Document\Listing();
            $list->setCondition("type = ? AND path LIKE ?", ['page', $obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        } else if ($obj->getType() === 'folder' && $obj instanceof \Pimcore\Model\Asset) {
            $list = new \Pimcore\Model\Asset\Listing();
            $list->setCondition("path LIKE ?", [$obj->getFullPath() . '/%']);
            $list->setOrderKey('LENGTH(path) ASC', false);
        }

        $children = $list->load();

        $excludePaths = [];

        if (!empty($children)) {

            /** @var \Pimcore\Model\AbstractModel $child */
            foreach ($children as $child) {

                $isNew = FALSE;
                $skip = FALSE;
                foreach ($excludePaths as $path) {
                    if (substr($child->getFullPath(), 0, strlen($path)) === $path) {
                        $skip = TRUE;
                        break;
                    }
                }

                if($skip === TRUE) {
                    continue;
                }

                $targetType = $obj->getType();
                if($cType === 'asset' && $targetType === 'folder') {
                    $targetType = 'asset';
                }

                try {
                    $restriction = Restriction::getByTargetId($child->getId(), $targetType);
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

                if($removeChildRestrictions == TRUE) {
                    $restriction->delete();
                } else if ($membersDocumentInheritable === TRUE) {
                    $restriction->setIsInherited(TRUE);
                    $restriction->save();
                } else if (!$restriction->getInherit()) {
                    $restriction->delete();
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