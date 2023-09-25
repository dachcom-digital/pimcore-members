<?php

namespace MembersBundle\Twig\Extension;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class SystemExtension extends AbstractExtension
{
    public function __construct(protected bool $oauthEnabled)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_system_oauth_enabled', [$this, 'oauthIsEnabled']),
        ];
    }

    public function oauthIsEnabled(): bool
    {
        return $this->oauthEnabled;
    }
}
