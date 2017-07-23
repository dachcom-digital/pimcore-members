<?php

namespace MembersBundle\EventListener\Connector;

use MembersBundle\Security\RestrictionUri;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LuceneSearchAssetRestriction implements EventSubscriberInterface
{
    /**
     * @var RestrictionUri
     */
    protected $restrictionUri;

    /**
     * LuceneSearchAssetRestriction constructor.
     *
     * @param RestrictionUri $restrictionUri
     */
    public function __construct(RestrictionUri $restrictionUri)
    {
        $this->restrictionUri = $restrictionUri;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'lucene_search.task.parser.asset_restriction' => 'checkAssetResource',
        ];
    }

    /**
     * @param \LuceneSearchBundle\Event\AssetResourceRestrictionEvent $event
     */
    public function checkAssetResource(\LuceneSearchBundle\Event\AssetResourceRestrictionEvent $event)
    {
        $uri = $event->getResource()->getUri()->toString();

        if (strpos($uri, 'members/request-data') !== FALSE) {
            try {
                $key = end(explode('/', $uri));
                $restrictedAssetInfo =  $this->restrictionUri->getAssetUrlInformation($key);
                if ($restrictedAssetInfo !== FALSE) {
                    $event->setAsset($restrictedAssetInfo['asset']);
                    $event->setRestrictions($restrictedAssetInfo['restrictionGroups']);
                }

            } catch(\ReflectionException $e) {
                \Pimcore\Logger::err($e->getMessage());
            }
        }
    }
}
