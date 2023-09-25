<?php

namespace MembersBundle\Manager;

use Pimcore\Model\DataObject\Listing;

interface ClassManagerInterface
{
    public function getGroupListing(): Listing;

    public function getUserListing(): Listing;

    public function getSsoIdentityListing(): Listing;

    public function getGroupClass(): string;

    public function getUserClass(): string;

    public function getSsoIdentityClass(): string;
}
