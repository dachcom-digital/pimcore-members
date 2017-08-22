<?php

namespace MembersBundle\Tool;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
