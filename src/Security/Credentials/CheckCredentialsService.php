<?php


namespace Pantheon\UserBundle\Security\Credentials;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

abstract class CheckCredentialsService implements CheckCredentialsServiceInterface
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

    protected $userClient;

    public function __construct(array $config, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->cache = $cache;
        $this->config = $config;
    }

    abstract function checkCredentials(string $login, string $pass):bool;

}