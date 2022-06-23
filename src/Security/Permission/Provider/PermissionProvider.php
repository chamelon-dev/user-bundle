<?php

namespace Pantheon\UserBundle\Security\Permission\Provider;


use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class PermissionProvider implements PermissionProviderInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var DateInterval
     */
    protected $expiredInterval;

    public function __construct(array $config, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
        $this->expiredInterval = (isset($config['expiredInterval'])) ? new DateInterval($config['expiredInterval']): new DateInterval("PT0S");
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
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


}