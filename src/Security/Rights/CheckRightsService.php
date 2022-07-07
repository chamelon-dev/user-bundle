<?php

namespace Pantheon\UserBundle\Security\Rights;

use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Service\UserRightsService;
use Symfony\Component\Security\Core\User\UserInterface;

class CheckRightsService implements CheckRightsServiceInterface
{
    private $userRightService;
    public function __construct(UserRightsService $userRightsService)
    {
        $this->userRightService = $userRightsService;
    }

    /**
     * @param UserInterface $user
     * @param string $permissionName Пешмишн роута.
     * @return bool
     */
    public function hasPermission(UserInterface $user, string $permissionName): bool
    {
        if (!($user instanceof User)) {
            return false;
        }
        $rolesText = $this->userRightService->getRolesValues($user);
        $permissionsText = $this->userRightService->getPermissionsValues($user);
        $isSuperAdmin = (isset($rolesText['ROLE_SUPER_ADMIN']) or isset($permissionsText['can everything']));
        if ($isSuperAdmin) {
            return true;
        }
        return isset($permissionsText[$permissionName]);
    }
}