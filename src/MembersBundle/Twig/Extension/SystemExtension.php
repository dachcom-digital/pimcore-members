<?php

namespace MembersBundle\Twig\Extension;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class SystemExtension extends AbstractExtension
{
    /**
     * @var bool
     */
    protected $oauthEnabled;

    /**
     * @param bool $oauthEnabled
     */
    public function __construct(bool $oauthEnabled)
    {
        $this->oauthEnabled = $oauthEnabled;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('members_system_oauth_enabled', [$this, 'oauthIsEnabled']),
        ];
    }

    /**
     * @return bool
     */
    public function oauthIsEnabled()
    {
        return $this->oauthEnabled;
    }
}
