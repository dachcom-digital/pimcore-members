<?php

namespace MembersBundle\Service;

use Symfony\Component\HttpFoundation\Request;

interface RequestPropertiesForUserExtractorServiceInterface
{
    public function extract(Request $request): array;

    public function extractFromParameterBag(array $parameter): array;
}
