<?php

namespace MembersBundle\Service;

use Pimcore\Model\Site;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Symfony\Component\HttpFoundation\Request;

class RequestPropertiesForUserExtractorService implements RequestPropertiesForUserExtractorServiceInterface
{
    /**
     * @var SiteResolver
     */
    protected $siteResolver;

    /**
     * @param SiteResolver $siteResolver
     */
    public function __construct(SiteResolver $siteResolver)
    {
        $this->siteResolver = $siteResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Request $request)
    {
        $userProperties = [
            '_user_locale' => $request->getLocale()
        ];

        if ($this->siteResolver->isSiteRequest()) {
            $userProperties['_site_domain'] = $this->siteResolver->getSite($request)->getMainDomain();
        }

        return $userProperties;
    }

    /**
     * {@inheritdoc}
     */
    public function extractFromParameterBag(array $parameter)
    {
        $userProperties = [];

        if (isset($parameter['locale']) && $parameter['locale'] !== null) {
            $userProperties['_user_locale'] = $parameter['locale'];
        }

        if (isset($parameter['site_id']) && $parameter['site_id'] !== null) {
            $site = Site::getById($parameter['site_id']);
            if ($site instanceof Site) {
                $userProperties['_site_domain'] = $site->getMainDomain();
            }
        }

        return $userProperties;
    }
}
