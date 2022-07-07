<?php

namespace Pantheon\UserBundle\Security\Credentials;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

class LocalCheckCredentialsService extends CheckCredentialsService
{
    protected $passwordEncoder;

    protected $userProvider;

    /**
     * @param array $config
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param UserProviderInterface $userProvider
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        array $config,
        CacheInterface $cache,
        LoggerInterface $logger,
        UserProviderInterface $userProvider,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        parent::__construct($config, $cache, $logger);
        $this->passwordEncoder = $passwordEncoder;
        $this->userProvider = $userProvider;
    }


    /**
     * @param string $login
     * @param string $pass
     * @return bool
     */
    public function checkCredentials(string $login, string $pass):bool
    {
        $user = $this->userProvider->loadUserByUsername($login);
        return $this->passwordEncoder->isPasswordValid($user, $pass);
    }
}
