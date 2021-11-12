<?php

namespace MembersBundle\Controller\Admin;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Manager\ClassManagerInterface;
use MembersBundle\Restriction\Restriction;
use MembersBundle\Service\RestrictionService;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use MembersBundle\Configuration\Configuration;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RestrictionController extends AdminController
{
    protected Configuration $configuration;
    protected ClassManagerInterface $classManager;
    protected RestrictionService $restrictionService;

    /**
     * @param Configuration         $configuration
     * @param ClassManagerInterface $classManager
     * @param RestrictionService    $restrictionService
     */
    public function __construct(
        Configuration $configuration,
        ClassManagerInterface $classManager,
        RestrictionService $restrictionService
    ) {
        $this->configuration = $configuration;
        $this->classManager = $classManager;
        $this->restrictionService = $restrictionService;
    }

    public function getGlobalSettingsAction(): JsonResponse
    {
        return $this->json(['settings' => $this->configuration->getConfigArray()]);
    }

    public function getGroupsAction(): JsonResponse
    {
        $list = $this->classManager->getGroupListing();

        $groups = [];
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

        $groups = array_filter(explode(',', $settings['membersDocumentUserGroups']));
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
