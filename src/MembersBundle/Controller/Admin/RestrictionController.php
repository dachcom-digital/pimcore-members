<?php

namespace MembersBundle\Controller\Admin;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Manager\ClassManager;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Restriction\RestrictionService;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use MembersBundle\Configuration\Configuration;
use Symfony\Component\HttpFoundation\Request;

class RestrictionController extends AdminController
{
    /**
     * @return string
     */
    public function getGlobalSettingsAction()
    {
        /** @var Configuration $configuration */
        $configuration = $this->get(Configuration::class);

        return $this->json(['settings' => $configuration->getConfigArray()]);
    }

    /**
     * @return string
     */
    public function getGroupsAction()
    {
        $list = $this->get(ClassManager::class)->getGroupListing();

        if ($list === false) {
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

            $data['leaf'] = true;
            $data['iconCls'] = 'pimcore_icon_roles';
            $data['allowChildren'] = false;

            $groups[] = $data;
        }

        return $this->json($groups);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDocumentRestrictionConfigAction(Request $request)
    {
        $documentId = $request->query->get('docId');
        $cType = $request->query->get('cType');

        $restriction = null;

        $isActive = false;
        $isInherited = false;
        $inherit = false;
        $userGroups = [];

        try {
            /** @var Restriction $restriction */
            $restriction = Restriction::getByTargetId($documentId, $cType);
        } catch (\Exception $e) {
        }

        if (!is_null($restriction)) {
            $isActive = true;
            $isInherited = $restriction->isInherited();
            $inherit = $restriction->getInherit();
            $userGroups = $restriction->getRelatedGroups();
            $cType = $restriction->getCtype();
        }

        return $this->json([
            'success'     => true,
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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function setDocumentRestrictionConfigAction(Request $request)
    {
        /** @var RestrictionService $restrictionService */
        $restrictionService = $this->get(RestrictionService::class);

        $data = json_decode($request->query->get('data'));

        $docId = (int)$data->docId;
        $settings = $data->settings;
        $cType = $data->cType; //object|page|asset

        $pimcoreType = 'document';
        if ($cType == 'object') {
            $pimcoreType = 'object';
        } elseif ($cType == 'asset') {
            $pimcoreType = 'asset';
        }

        $obj = \Pimcore\Model\Element\Service::getElementById($pimcoreType, $docId);

        $inheritableState = $settings->membersDocumentInheritable;

        if ($inheritableState === 'on') {
            $inheritable = true;
        } elseif (is_null($inheritableState)) {
            $inheritable = false;
        } else {
            $inheritable = false;
        }

        $groups = array_filter(explode(',', $settings->membersDocumentUserGroups));
        $restriction = $restrictionService->createRestriction($obj, $cType, $inheritable, false, $groups);

        return $this->json([
            'success'    => true,
            'isActive'   => !empty($groups),
            'docId'      => (int)$settings->docId,
            'userGroups' => !empty($groups) ? $restriction->getRelatedGroups() : []
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteDocumentRestrictionConfigAction(Request $request)
    {
        $data = json_decode($request->query->get('data'));

        $docId = (int)$data->docId;
        $cType = $data->cType; //object|page|asset

        $restriction = false;

        try {
            $restriction = Restriction::getByTargetId($docId, $cType);
        } catch (\Exception $e) {
        }

        //restriction has been disabled! remove everything!
        if ($restriction !== false) {
            $restriction->getDao()->delete();
        }

        return $this->json(['success' => true]);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getNextParentRestrictionAction(Request $request)
    {
        /** @var RestrictionService $restrictionService */
        $restrictionService = $this->get(RestrictionService::class);

        $elementId = $request->query->get('docId');
        $cType = $request->query->get('cType');
        $closestInheritanceParent = $restrictionService->findClosestInheritanceParent($elementId, $cType);

        return $this->json([
            'success' => true,
            'key'     => $closestInheritanceParent['key'],
            'path'    => $closestInheritanceParent['path']
        ]);
    }

}
