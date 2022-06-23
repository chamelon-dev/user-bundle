<?php

namespace Pantheon\UserBundle\Security\Permission\Service;


use Pantheon\UserBundle\Security\Credentials\CheckCredentialsServiceInterface;
use Pantheon\UserBundle\Security\Permission\Provider\PermissionProviderInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

abstract class PermissionService implements PermissionServiceInterface
{

    /**
     * @var PermissionProviderInterface
     */
    protected $permissionProvider;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CheckCredentialsServiceInterface
     */
    protected $checkCredentialsService;

    /**
     * PermissionServiceInterface constructor.
     * @param PermissionProviderInterface $permissionProvider
     * @param CheckCredentialsServiceInterface $checkCredentialsService
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(PermissionProviderInterface $permissionProvider, CheckCredentialsServiceInterface $checkCredentialsService, CacheInterface $cache, LoggerInterface $logger){
        $this->permissionProvider = $permissionProvider;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->checkCredentialsService = $checkCredentialsService;
    }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
       return $this->logger;
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    abstract function getUserPermissions(UserInterface $user): array;

    /**
     * @param UserInterface $user
     * @param string $permissionName
     * @return bool
     */
    public function hasUserPermission(UserInterface $user, string $permissionName):bool{
        try {
            $userMemberOfAttr = $this->getUserPermissions($user);
            $permissions = [
                "ROLE_USER"=>"ROLE_USER",
                "IS_AUTHENTICATED_ANONYMOUSLY"=>"IS_AUTHENTICATED_ANONYMOUSLY"
            ];
            foreach ($userMemberOfAttr as $innerPermissionName=>$dn){
                $permissions[$innerPermissionName] = $dn;
            }
            if(isset($permissions[$permissionName])){
                return true;
            }
        } catch (InvalidArgumentException $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
        return false;
    }

    /**
     * @param string $login
     * @param string $pass
     * @return bool
     */
    public function checkCredentials(string $login, string $pass): bool{
        return $this->checkCredentialsService->checkCredentials($login,$pass);
    }

    /**
     * @return PermissionProviderInterface
     */
    public function getPermissionProvider(): PermissionProviderInterface
    {
        return $this->permissionProvider;
    }

    /**
     * @return CheckCredentialsServiceInterface
     */
    public function getCheckCredentialsService(): CheckCredentialsServiceInterface
    {
        return $this->checkCredentialsService;
    }

    public function getCurrentUserToken(){
        return null;
    }

}