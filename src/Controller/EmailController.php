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

namespace MembersBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends AbstractController
{
    public function emailAction(Request $request): Response
    {
        return $this->renderTemplate('@Members/email/email.html.twig', array_filter($request->attributes->all(), static function ($parameterKey) {
            return !str_starts_with($parameterKey, '_');
        }, ARRAY_FILTER_USE_KEY));
    }
}
