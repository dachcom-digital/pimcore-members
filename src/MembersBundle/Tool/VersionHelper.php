<?php

namespace MembersBundle\Tool;

class VersionHelper
{
    public static function pimcoreVersionIsEqualThan(string $version): int|bool
    {
        return version_compare(self::getPimcoreVersion(), $version, '=');
    }

    public static function pimcoreVersionIsGreaterThan(string $version): int|bool
    {
        return version_compare(self::getPimcoreVersion(), $version, '>');
    }

    public static function pimcoreVersionIsGreaterOrEqualThan(string $version): int|bool
    {
        return version_compare(self::getPimcoreVersion(), $version, '>=');
    }

    public static function pimcoreVersionIsLowerThan(string $version): int|bool
    {
        return version_compare(self::getPimcoreVersion(), $version, '<');
    }

    public static function pimcoreVersionIsLowerOrEqualThan(string $version): int|bool
    {
        return version_compare(self::getPimcoreVersion(), $version, '<=');
    }

    private static function getPimcoreVersion(): string
    {
        return preg_replace('/[^0-9.]/', '', \Pimcore\Version::getVersion());
    }
}
