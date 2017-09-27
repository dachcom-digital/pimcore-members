<?php

namespace MembersBundle\Controller\Admin;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Manager\ClassManager;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Restriction\RestrictionService;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use MembersBundle\Configuration\Configuration;
use Pimcore\Model\Listing\AbstractListing;
use Symfony\Component\HttpFoundation\Request;

class RestrictionController extends AdminController
{
    /**
     * @return string
     */
    public function getGlobalSettingsAction()
    {
        /** @var Configuration $configuration */
        $configuration = $this->container->get(Configuration::class);

        return $this->json(['settings' => $configuration->getConfigArray()]);
    }

    /**
     * @return string
     */
    public function getGroupsAction()
    {
        /** @var AbstractListing $list */
        $list = $this->container->get(ClassManager::class)->getGroupListing();

        if ($list === FALSE) {
            return $this->json([]);
        }

        $groups = [];
        /** @var GroupInterface $group */
        foreach ($list->load() as $group) {
            $data = [
                'id'          => $group->getId(),
                'name'        => $group->getName(),
                'elementType' => 'group',
                'qtipCfg'     => [
                    'title' => 'ID: ' . $group->getId()
                ]
            ];

            $data['leaf'] = TRUE;
            $data['iconCls'] = 'pimcore_icon_roles';
            $data['allowChildren'] = FALSE;

            $groups[] = $data;
        }

        return $this->json($groups);
    }

    /**
     * @param Request $request
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function getDocumentRestrictionConfigAction(Request $request)
    {
        $documentId = $request->query->get('docId');
        $cType = $request->query->get('cType');

        $restriction = NULL;

        $isActive = FALSE;
        $isInherited = FALSE;
        $inherit = FALSE;
        $userGroups = [];

        try {
            /** @var Restriction $restriction */
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

        return $this->json([
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
     * @param Request $request
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function setDocumentRestrictionConfigAction(Request $request)
    {
        /** @var RestrictionService $restrictionService */
        $restrictionService = $this->container->get(RestrictionService::class);

        $data = json_decode($request->query->get('data'));

        $docId = (int)$data->docId;
        $settings = $data->settings;
        $cType = $data->cType; //object|page|asset

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

        //remove restriction since no group is selected any more.
        if (empty($settings->membersDocumentUserGroups)) {
            if ($hasRestriction === TRUE) {
                $restriction->delete();
            }
            //update or set restriction
        } else {

            $active = TRUE;

            $membersDocumentInheritable = $settings->membersDocumentInheritable;
            $membersDocumentUserGroups = explode(',', $settings->membersDocumentUserGroups);

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

        $restrictionService->checkRestrictionContext($obj, $cType);

        //clear cache!
        \Pimcore\Cache::clearTag('members');

        return $this->json([
            'success'    => TRUE,
            'isActive'   => $active,
            'docId'      => (int)$settings->docId,
            'userGroups' => $active ? $restriction->getRelatedGroups() : []
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function deleteDocumentRestrictionConfigAction(Request $request)
    {
        $data = json_decode($request->query->get('data'));

        $docId = (int)$data->docId;
        $cType = $data->cType; //object|page

        $restriction = FALSE;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        //restriction has been disabled! remove everything!
        if ($restriction !== FALSE) {
            $restriction->delete();
        }

        return $this->json(['success' => TRUE]);
    }

    /**
     * @param Request $request
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function getNextParentRestrictionAction(Request $request)
    {
        /** @var RestrictionService $restrictionService */
        $restrictionService = $this->container->get(RestrictionService::class);

        $elementId = $request->query->get('docId');
        $cType = $request->query->get('cType');
        $closestInheritanceParent = $restrictionService->findClosestInheritanceParent($elementId, $cType);

        return $this->json([
            'success' => TRUE,
            'key'     => $closestInheritanceParent['key'],
            'path'    => $closestInheritanceParent['path']
        ]);
    }

}
