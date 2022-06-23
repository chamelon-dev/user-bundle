<?php

namespace Pantheon\UserBundle\Repository;

use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findWithRole(Role $role)
    {
        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.role', 'r')
            ->where('r.id = :role_id')
            ->setParameter('role_id', $role)
            ->getQuery()
            ->getResult()
        ;
    }
}
