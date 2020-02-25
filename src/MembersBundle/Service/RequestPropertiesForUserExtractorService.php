<?php

namespace MembersBundle\Service;

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
     * {@inheritDoc}
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
}
