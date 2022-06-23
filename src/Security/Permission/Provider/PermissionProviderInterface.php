<?php

namespace Pantheon\UserBundle\Security\Permission\Provider;


use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Contracts\Cache\CacheInterface;

interface PermissionProviderInterface
{
    /**
     * @return array
     */
    public function getConfig(): array;

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface;

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface;

}