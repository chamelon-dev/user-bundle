<?php

namespace Pantheon\UserBundle\Security\Rights;

use Symfony\Component\Security\Core\User\UserInterface;

interface CheckRightsServiceInterface
{
    /**
     * @param UserInterface $user
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(UserInterface $user, string $permissionName) : bool;
}