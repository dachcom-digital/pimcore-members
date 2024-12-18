<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Controller\Admin;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Manager\ClassManagerInterface;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Service\RestrictionService;
use Pimcore\Bundle\AdminBundle\Controller\AdminAbstractController;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RestrictionController extends AdminAbstractController
{
    public function __construct(
        protected Configuration $configuration,
        protected ClassManagerInterface $classManager,
        protected RestrictionService $restrictionService
    ) {
    }

    public function getGlobalSettingsAction(): JsonResponse
    {
        return $this->json([
            'settings' => $this->configuration->getConfigArray()
        ]);
    }

    public function getGroupsAction(): JsonResponse
    {
        $groups = [];
        $list = $this->classManager->getGroupListing();

        /** @var GroupInterface $group */
        foreach ($list->getObjects() as $group) {
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

    public function getDocumentRestrictionConfigAction(Request $request): JsonResponse
    {
        $documentId = $request->query->get('docId');
        $cType = $request->query->get('cType');

        $restriction = null;

        $isActive = false;
        $isInherited = false;
        $inherit = false;
        $userGroups = [];

        try {
            $restriction = Restriction::getByTargetId($documentId, $cType);
        } catch (\Exception $e) {
            // fail silently
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
            'docId'       => (int) $documentId,
            'cType'       => $cType,
            'isActive'    => $isActive,
            'isInherited' => $isInherited,
            'inherit'     => $inherit,
            'userGroups'  => $userGroups
        ]);
    }

    public function setDocumentRestrictionConfigAction(Request $request): JsonResponse
    {
        $data = json_decode($request->query->get('data'), true, 512, JSON_THROW_ON_ERROR);

        $documentId = (int) $data['docId'];
        $settings = $data['settings'] ?? null;
        $cType = $data['cType'] ?? null; //object|page|asset

        $pimcoreType = 'document';
        if ($cType === 'object') {
            $pimcoreType = 'object';
        } elseif ($cType === 'asset') {
            $pimcoreType = 'asset';
        }

        $obj = Service::getElementById($pimcoreType, $documentId);

        if (!$obj instanceof ElementInterface) {
            return $this->json(['success' => false]);
        }

        $inheritableState = $settings['membersDocumentInheritable'] ?? null;
        $inheritable = $inheritableState === 'on';

        $groups = array_filter(explode(',', $settings['membersDocumentUserGroups'] ?? ''));

        $restriction = $this->restrictionService->createRestriction($obj, $cType, $inheritable, false, $groups);

        return $this->json([
            'success'    => true,
            'isActive'   => !empty($groups),
            'docId'      => $documentId,
            'userGroups' => $restriction instanceof Restriction ? $restriction->getRelatedGroups() : []
        ]);
    }

    public function getNextParentRestrictionAction(Request $request): JsonResponse
    {
        $elementId = $request->query->get('docId');
        $cType = $request->query->get('cType');
        $closestInheritanceParent = $this->restrictionService->findClosestInheritanceParent($elementId, $cType);

        return $this->json([
            'success' => true,
            'key'     => $closestInheritanceParent['key'],
            'path'    => $closestInheritanceParent['path']
        ]);
    }
}
