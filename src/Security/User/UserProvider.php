<?php

namespace Pantheon\UserBundle\Security\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $classOrAlias;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $em
     * @param string $classOrAlias
     * @param string|null $property
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $em, string $classOrAlias, string $property = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->em = $em;
        $this->classOrAlias = $classOrAlias;
        $this->property = $property;
    }

    /**
     * @param string $username
     * @return object|UserInterface
     */
    public function loadUserByUsername($username)
    {
        $repository = $this->getRepository();
        if (null !== $this->property) {
            $user = $repository->findOneBy([$this->property => $username, 'isActive'=>true]);
        } else {
            if (!$repository instanceof UserLoaderInterface) {
                throw new \InvalidArgumentException(sprintf('You must either make the "%s" entity Doctrine Repository ("%s") implement "Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface" or set the "property" option in the corresponding entity provider configuration.', $this->classOrAlias, \get_class($repository)));
            }

            $user = $repository->loadUserByUsername($username);
            if($user && !$user->isActive()){
                $user = null;
            }
        }
        if (null === $user) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        return $user;
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     */
    public function updateLastLoginDate(UserInterface $user): UserInterface
    {
        if(method_exists($user,'setLastLogin')){
            $user->setLastLogin(new DateTime());
            $this->em->persist($user);
            $this->em->flush();
        }
        return $user;
    }

    /**
     *
     * @param UserInterface $user
     * @return UserInterface
     *
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
        $repository = $this->getRepository();
        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            if (!$id = $this->getClassMetadata()->getIdentifierValues($user)) {
                throw new \InvalidArgumentException('You cannot refresh a user from the EntityUserProvider that does not contain an identifier. The user object has to be serialized with its own identifier mapped by Doctrine.');
            }
            $refreshedUser = $repository->find($id);
            if (null === $refreshedUser) {
                throw new UsernameNotFoundException('User with id '.json_encode($id).' not found.');
            }
        }

        return $refreshedUser;
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass() || is_subclass_of($class, $this->getClass());
    }

    private function getRepository()
    {
        return $this->em->getRepository($this->classOrAlias);
    }

    private function getClass(): string
    {
        if (null === $this->class) {
            $class = $this->classOrAlias;
            if (false !== strpos($class, ':')) {
                $class = $this->getClassMetadata()->getName();
            }
            $this->class = $class;
        }
        return $this->class;
    }

    private function getClassMetadata(): ClassMetadata
    {
        return $this->em->getClassMetadata($this->classOrAlias);
    }
}