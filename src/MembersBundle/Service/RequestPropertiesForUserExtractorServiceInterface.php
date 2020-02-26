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

    /**
     * @param array $parameter
     *
     * @return mixed
     */
    public function extractFromParameterBag(array $parameter);
}
