<?php

namespace MembersBundle\Adapter\Group;

interface GroupInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @return string[]|null
     */
    public function getRoles();
}
