<?php

namespace Pantheon\UserBundle\Security\Rights;

use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Service\UserRightsService;
use Symfony\Component\Security\Core\User\UserInterface;

//abstract
class CheckRightsService implements CheckRightsServiceInterface
{
    private $userRightService;
    public function __construct(UserRightsService $userRightsService)
    {
        $this->userRightService = $userRightsService;
    }

    /**
     * Есть ли у юзера пермишн.
     *
     * @param UserInterface $user
     * @param string $permissionName Пешмишн роута.
     * @return bool
     */
    public function hasPermission(UserInterface $user, string $permissionName): bool
    {
        // TODO: нужно ли проверять credentials?
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


//    /**
//     * @var PermissionProviderInterface
//     */
//    protected $permissionProvider;
//
//    /**
//     * @var CacheInterface
//     */
//    protected $cache;
//
//    /**
//     * @var LoggerInterface
//     */
//    protected $logger;
//
//    /**
//     * @var CheckCredentialsServiceInterface
//     */
//    protected $checkCredentialsService;
//
//    /**
//     * PermissionServiceInterface constructor.
//     * @param PermissionProviderInterface $permissionProvider
//     * @param CheckCredentialsServiceInterface $checkCredentialsService
//     * @param CacheInterface $cache
//     * @param LoggerInterface $logger
//     */
//    public function __construct(PermissionProviderInterface $permissionProvider, CheckCredentialsServiceInterface $checkCredentialsService, CacheInterface $cache, LoggerInterface $logger){
//        $this->permissionProvider = $permissionProvider;
//        $this->cache = $cache;
//        $this->logger = $logger;
//        $this->checkCredentialsService = $checkCredentialsService;
//    }
//
//    /**
//     * @return CacheInterface
//     */
//    public function getCache(): CacheInterface
//    {
//        return $this->cache;
//    }
//
//    /**
//     * @return LoggerInterface
//     */
//    public function getLogger(): LoggerInterface
//    {
//       return $this->logger;
//    }
//
//    /**
//     * @param UserInterface $user
//     * @return array
//     */
//    abstract function getUserPermissions(UserInterface $user): array;
//
//    /**
//     * @param UserInterface $user
//     * @param string $permissionName
//     * @return bool
//     */
//    public function hasUserPermission(UserInterface $user, string $permissionName):bool{
//        try {
//            $userMemberOfAttr = $this->getUserPermissions($user);
//            $permissions = [
//                "ROLE_USER"=>"ROLE_USER",
//                "IS_AUTHENTICATED_ANONYMOUSLY"=>"IS_AUTHENTICATED_ANONYMOUSLY"
//            ];
//            foreach ($userMemberOfAttr as $innerPermissionName=>$dn){
//                $permissions[$innerPermissionName] = $dn;
//            }
//            if(isset($permissions[$permissionName])){
//                return true;
//            }
//        } catch (InvalidArgumentException $exception) {
//            $this->getLogger()->error($exception->getMessage());
//        }
//        return false;
//    }
//
//    /**
//     * @param string $login
//     * @param string $pass
//     * @return bool
//     */
//    public function checkCredentials(string $login, string $pass): bool{
//        return $this->checkCredentialsService->checkCredentials($login,$pass);
//    }
//
//    /**
//     * @return PermissionProviderInterface
//     */
//    public function getPermissionProvider(): PermissionProviderInterface
//    {
//        return $this->permissionProvider;
//    }
//
//    /**
//     * @return CheckCredentialsServiceInterface
//     */
//    public function getCheckCredentialsService(): CheckCredentialsServiceInterface
//    {
//        return $this->checkCredentialsService;
//    }
//
//    public function getCurrentUserToken(){
//        return null;
//    }

}