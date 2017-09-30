<?php

namespace MembersBundle\Manager;

interface ClassManagerInterface
{
    /**
     * @return bool
     */
    public function getGroupListing();

    /**
     * @return bool
     */
    public function getUserListing();

    /**
     * @return bool|string
     */
    public function getUserClass();
}