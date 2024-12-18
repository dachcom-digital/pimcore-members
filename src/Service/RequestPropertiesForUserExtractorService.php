<?php

namespace MembersBundle\Service;

use Pimcore\Model\Site;
use Pimcore\Http\Request\Resolver\SiteResolver;
use Symfony\Component\HttpFoundation\Request;

class RequestPropertiesForUserExtractorService implements RequestPropertiesForUserExtractorServiceInterface
{
    public function __construct(protected SiteResolver $siteResolver)
    {
    }

    public function extract(Request $request): array
    {
        $userProperties = [
            '_user_locale' => $request->getLocale()
        ];

        if ($this->siteResolver->isSiteRequest()) {
            $userProperties['_site_domain'] = $this->siteResolver->getSite($request)?->getMainDomain();
        }

        return $userProperties;
    }

    public function extractFromParameterBag(array $parameter): array
    {
        $userProperties = [];

        if (isset($parameter['locale'])) {
            $userProperties['_user_locale'] = $parameter['locale'];
        }

        if (isset($parameter['site_id'])) {
            $site = Site::getById($parameter['site_id']);
            if ($site instanceof Site) {
                $userProperties['_site_domain'] = $site->getMainDomain();
            }
        }

        return $userProperties;
    }
}
