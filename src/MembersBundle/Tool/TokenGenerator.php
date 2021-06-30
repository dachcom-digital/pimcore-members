<?php

namespace MembersBundle\Tool;

class TokenGenerator implements TokenGeneratorInterface
{
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
