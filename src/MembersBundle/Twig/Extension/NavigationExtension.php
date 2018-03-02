<?php

namespace MembersBundle\Twig\Extension;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Document;
use Pimcore\Navigation\Container;
use Pimcore\Templating\Helper\Navigation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NavigationExtension extends \Twig_Extension
{
    /**
     * @var Navigation
     */
    private $navigationHelper;

    /**
     * @var RestrictionManagerInterface
     */
    private $restrictionManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param Navigation                  $navigationHelper
     * @param RestrictionManagerInterface $restrictionManager
     * @param TokenStorageInterface       $tokenStorage
     */
    public function __construct(
        Navigation $navigationHelper,
        RestrictionManagerInterface $restrictionManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->navigationHelper = $navigationHelper;
        $this->restrictionManager = $restrictionManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_Function('members_build_nav', [$this, 'buildNavigation']),
        ];
    }

    /**
     * @param Document      $activeDocument
     * @param Document|null $navigationRootDocument
     * @param string|null   $htmlMenuPrefix
     * @param bool          $cache
     *
     * @return Container
     */
    public function buildNavigation(
        Document $activeDocument,
        Document $navigationRootDocument = NULL,
        string $htmlMenuPrefix = NULL,
        $cache = TRUE
    ): Container {

        $cacheKey = $cache;
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!\Pimcore\Tool::isFrontendRequestByAdmin() && $cacheKey !== FALSE) {

            $mergedCacheKey = is_bool($cache) ? '' : $cache;

            if ($user instanceof UserInterface) {
                $allowedGroups = $user->getGroups();

                $groupIds = [];
                if (!empty($allowedGroups)) {
                    /** @var GroupInterface $group */
                    foreach ($allowedGroups as $group) {
                        $groupIds[] = $group->getId();
                    }

                    if (!empty($groupIds)) {
                        $cacheKey = ltrim($mergedCacheKey . '-' . implode('-', $groupIds), '-');
                    }
                }
            }
        }

        return $this->navigationHelper->buildNavigation(
            $activeDocument,
            $navigationRootDocument,
            $htmlMenuPrefix,
            function (\Pimcore\Navigation\Page\Document $document, AbstractModel $page) {
                $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($page);
                if ($restrictionElement->getSection() !== RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
                    $document->setActive(FALSE);
                    $document->setVisible(FALSE);
                }

                return $page;
            },
            $cacheKey
        );
    }
}
