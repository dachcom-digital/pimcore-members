<?php

namespace MembersBundle\Tool;

interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}
