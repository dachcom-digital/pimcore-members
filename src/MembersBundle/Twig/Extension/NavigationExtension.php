<?php

namespace MembersBundle\Twig\Extension;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Document;
use Pimcore\Navigation\Container;
use Pimcore\Twig\Extension\Templating\Navigation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @see \Pimcore\Twig\Extension\NavigationExtension
 */
class NavigationExtension extends AbstractExtension
{
    protected Navigation $navigationHelper;
    protected RestrictionManagerInterface $restrictionManager;
    protected TokenStorageInterface $tokenStorage;

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
            new TwigFunction('members_build_nav', [$this, 'buildNavigation']),
        ];
    }

    public function buildNavigation(
        array|Document $params = null,
        Document $navigationRootDocument = null,
        string $htmlMenuPrefix = null,
        bool|string $cache = true
    ): Container {
        if (is_array($params)) {
            return $this->buildMembersNavigation($params);
        }

        // using deprecated argument configuration ($params = navigation root document)
        return $this->legacyBuildNavigation(
            $params,
            $navigationRootDocument,
            $htmlMenuPrefix,
            $cache
        );
    }

    protected function buildMembersNavigation(array $params): Container
    {
        // Update cache key and page callback
        $params['cache'] = $this->getCacheKey($params['cache'] ?? true);
        $params['pageCallback'] = $this->getPageCallback($params['pageCallback'] ?? null);

        if (!method_exists($this->navigationHelper, 'build')) {
            throw new \Exception(
                'Navigation::build() unavailable, update your Pimcore version to >= 6.5',
                1605864272
            );
        }

        return $this->navigationHelper->build($params);
    }

    protected function getCacheKey(bool|string $cache): bool|string
    {
        $cacheKey = $cache;
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (\Pimcore\Tool::isFrontendRequestByAdmin() || $cacheKey === false || !($user instanceof UserInterface)) {
            return $cacheKey;
        }

        $allowedGroups = $user->getGroups();
        $groupIds = [];
        if (!empty($allowedGroups)) {
            /** @var GroupInterface $group */
            foreach ($allowedGroups as $group) {
                $groupIds[] = $group->getId();
            }

            if (!empty($groupIds)) {
                $mergedCacheKey = is_bool($cache) ? '' : $cache;
                $cacheKey = ltrim($mergedCacheKey . '-' . implode('-', $groupIds), '-');
            }
        }

        return $cacheKey;
    }

    protected function getPageCallback(?\Closure $additionalClosure = null): \Closure
    {
        return function (\Pimcore\Navigation\Page\Document $document, AbstractModel $page) use ($additionalClosure) {
            $restrictionElement = $this->applyPageRestrictions($document, $page);

            // Call additional closure if configured and also pass restriction element as additional argument
            if ($additionalClosure !== null) {
                $additionalClosure->call($this, $document, $page, $restrictionElement);
            }

            return $page;
        };
    }

    protected function applyPageRestrictions(\Pimcore\Navigation\Page\Document $document, AbstractModel $page): ElementRestriction
    {
        $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($page);
        if ($restrictionElement->getSection() !== RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
            $document->setActive(false);
            $document->setVisible(false);
        }

        return $restrictionElement;
    }

    protected function legacyBuildNavigation(
        Document $activeDocument,
        ?Document $navigationRootDocument = null,
        ?string $htmlMenuPrefix = null,
        bool|string$cache = true
    ): Container {
        return $this->navigationHelper->buildNavigation(
            $activeDocument,
            $navigationRootDocument,
            $htmlMenuPrefix,
            $this->getPageCallback(),
            $this->getCacheKey($cache)
        );
    }
}
