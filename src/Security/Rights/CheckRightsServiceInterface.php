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


//
//    public function getCurrentUserToken();
//
//    /**
//     * @param UserInterface $user
//     * @return array
//     */
//    public function getUserPermissions(UserInterface $user): array;
//
//    /**
//     * @param UserInterface $user
//     * @param string $permissionName
//     * @return bool
//     */
//    public function hasUserPermission(UserInterface $user,string $permissionName):bool;
//
//    /**
//     * @return PermissionProviderInterface
//     */
//    public function getPermissionProvider(): PermissionProviderInterface;
//
//    /**
//     * @param string $login
//     * @param string $pass
//     * @return bool
//     */
//    public function checkCredentials(string $login, string $pass):bool;
//
//    /**
//     * @return CacheInterface
//     */
//    public function getCache(): CacheInterface;
//
//    /**
//     * @return LoggerInterface
//     */
//    public function getLogger(): LoggerInterface;

}