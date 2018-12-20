<?php

namespace DachcomBundle\Test\Util;

class MembersHelper
{
    const AREA_TEST_NAMESPACE = 'dachcomBundleTest';

    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     * @param null   $data
     *
     * @return string
     */
    public static function generateEditableConfiguration(string $name, string $type, array $options, $data = null)
    {
        $dotSuffix = VersionHelper::pimcoreVersionIsGreaterOrEqualThan('5.5.0') ? '_' : '.';
        $colonSuffix = VersionHelper::pimcoreVersionIsGreaterOrEqualThan('5.5.0') ? '_' : ':';
        $prettyJson = VersionHelper::pimcoreVersionIsGreaterOrEqualThan('5.5.4');

        $editableConfig = [
            'id'        => sprintf('pimcore_editable_%s%s1%s%s', self::AREA_TEST_NAMESPACE, $colonSuffix, $dotSuffix, $name),
            'name'      => sprintf('%s:1.%s', self::AREA_TEST_NAMESPACE, $name),
            'realName'  => $name,
            'options'   => $options,
            'data'      => $data,
            'type'      => $type,
            'inherited' => false,
        ];

        $data = sprintf('editableConfigurations.push(%s);', json_encode($editableConfig, ($prettyJson ? JSON_PRETTY_PRINT : JSON_ERROR_NONE)));

        return $data;
    }
}
