<?php

namespace Pantheon\UserBundle\Security\Permission\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\CacheInterface;

class LocalPermissionProvider extends PermissionProvider
{
    protected $permissions;

    /**
     * LocalPermissionProvider constructor.
     * @param array $config
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(array $config, CacheInterface $cache, LoggerInterface $logger)
    {
        parent::__construct($config, $cache, $logger);
        $this->permissions = new ArrayCollection();
        $this->loadPermissions();
    }

    /**
     * @param array $roles
     * @return array
     */
    public function getUserPermissions(array $roles)
    {
        $perms = [];
        foreach ($roles as $role) {
            $items = $this->getPermissions()->get($role);
            if ($items) {
                foreach ($items as $item) {
                    $perms[$item] = $item;
                }
            }
        }
        return $perms;
    }

    protected function loadPermissions()
    {
        $config = $this->getConfig();
        $projectDir = $config['projectDir'];
        $items = Yaml::parseFile($projectDir . '/' . $config['permissionsFile']);
        $permissions = $this->getPermissions();
        if (isset($items['roles'])) {
            foreach ($items['roles'] as $role => $item) {
                $permissions->set($role, $item);
            }
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getPermissions(): ArrayCollection
    {
        return $this->permissions;
    }
}
