<?php

namespace MembersBundle\Tool;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
