<?php

namespace MembersBundle\Twig\Extension;

use MembersBundle\Adapter\Group\GroupInterface;
use MembersBundle\Adapter\User\UserInterface;
use MembersBundle\Manager\RestrictionManager;
use MembersBundle\Manager\RestrictionManagerInterface;
use MembersBundle\Restriction\ElementRestriction;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Navigation\Container;
use Pimcore\Navigation\Page\Document;
use Pimcore\Tool;
use Pimcore\Twig\Extension\Templating\Navigation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    protected Navigation $navigationExtension;
    protected RestrictionManagerInterface $restrictionManager;
    protected TokenStorageInterface $tokenStorage;

    public function __construct(
        Navigation $navigationExtension,
        RestrictionManagerInterface $restrictionManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->navigationExtension = $navigationExtension;
        $this->restrictionManager = $restrictionManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_build_nav', [$this, 'buildNavigation']),
        ];
    }

    public function buildNavigation(array $params): Container
    {
        return $this->buildMembersNavigation($params);
    }

    protected function buildMembersNavigation(array $params): Container
    {
        // Update cache key and page callback
        $params['cache'] = $this->getCacheKey($params['cache'] ?? true);
        $params['pageCallback'] = $this->getPageCallback($params['pageCallback'] ?? null);

        return $this->navigationExtension->build($params);
    }

    protected function getCacheKey(mixed $cache): mixed
    {
        $cacheKey = $cache;
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if ($cacheKey === false || !($user instanceof UserInterface) || Tool::isFrontendRequestByAdmin()) {
            return $cacheKey;
        }

        $groupIds = [];
        $allowedGroups = $user->getGroups();

        if (!empty($allowedGroups)) {

            /** @var GroupInterface $group */
            foreach ($allowedGroups as $group) {
                $groupIds[] = $group->getId();
            }

            $mergedCacheKey = is_bool($cache) ? '' : $cache;
            $cacheKey = ltrim($mergedCacheKey . '-' . implode('-', $groupIds), '-');
        }

        return $cacheKey;
    }

    protected function getPageCallback(?\Closure $additionalClosure = null): \Closure
    {
        return function (Document $document, ElementInterface $page) use ($additionalClosure) {
            $restrictionElement = $this->applyPageRestrictions($document, $page);

            // Call additional closure if configured and also pass restriction element as additional argument
            if ($additionalClosure !== null) {
                $additionalClosure->call($this, $document, $page, $restrictionElement);
            }

            return $page;
        };
    }

    protected function applyPageRestrictions(Document $document, ElementInterface $page): ElementRestriction
    {
        $restrictionElement = $this->restrictionManager->getElementRestrictionStatus($page);
        if ($restrictionElement->getSection() !== RestrictionManager::RESTRICTION_SECTION_ALLOWED) {
            $document->setActive(false);
            $document->setVisible(false);
        }

        return $restrictionElement;
    }
}
