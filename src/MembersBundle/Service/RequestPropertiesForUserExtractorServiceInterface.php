<?php

namespace MembersBundle\Service;

use Symfony\Component\HttpFoundation\Request;

interface RequestPropertiesForUserExtractorServiceInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function extract(Request $request);
}
