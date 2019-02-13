<?php

# prevent php warnings in phpstan.
if (!defined('PIMCORE_PRIVATE_VAR')) {
    define('PIMCORE_PRIVATE_VAR', '');
}

/** hide class alias in IDE code completion */
$hrefClass = '\\Pimcore\\Model\\Document\\Tag\\Href';
class_alias(\Pimcore\Model\Document\Tag\Relation::class, $hrefClass, true);