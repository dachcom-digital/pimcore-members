<?php

namespace MembersBundle\Manager;

use Pimcore\Model\DataObject\Listing;

interface ClassManagerInterface
{
    /**
     * @return Listing
     */
    public function getGroupListing();

    /**
     * @return Listing
     */
    public function getUserListing();

    /**
     * @return Listing
     */
    public function getSsoIdentityListing();

    /**
     * @return string
     */
    public function getGroupClass();

    /**
     * @return string
     */
    public function getUserClass();

    /**
     * @return string
     */
    public function getSsoIdentityClass();
}
