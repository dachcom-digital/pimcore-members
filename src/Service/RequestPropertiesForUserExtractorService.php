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

namespace MembersBundle\Service;

use Pimcore\Http\Request\Resolver\SiteResolver;
use Pimcore\Model\Site;
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
