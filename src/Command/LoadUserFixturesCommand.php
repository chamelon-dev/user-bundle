<?php

namespace Pantheon\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Загрузить фикстуры пользователей, ролей, пермишнов.
 */
class LoadUserFixturesCommand extends Command
{
    protected static $defaultName = 'app:load-user-fixtures';

    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository,
        UserPasswordEncoderInterface $encoder
    )
    {
        parent::__construct();
        $this->kernel = $kernel;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->roleRepositoru = $roleRepository;
        $this->permissionRepository = $permissionRepository;
        $this->encoder = $encoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Загрузить фикстуры пользователей из файла Resources/config/fixtures/users.json')
            ->addArgument(
                'json',
                InputArgument::OPTIONAL,
                'путь к файлу, по умолчанию (e.g. <fg=yellow>Resources/config/fixtures/users.json</>)',
                'Resources/config/fixtures/users.json'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->kernel->locateResource('@UserBundle') . $input->getArgument('json');
        $json = file_get_contents($file);
        $users = json_decode($json, true);
        foreach ($users as $userData) {
            $roles = [];
            if (isset($userData['roles'])) {
                foreach ($userData['roles'] as $roleData) {
                    $permissions = [];
                    if (isset($roleData['permissions'])) {
                        foreach ($roleData['permissions'] as $permissionData) {
                            $permisson = (new Permission())
                                ->setName($permissionData['name'])
                                ->setTitle($permissionData['title'] ?? null)
                                ->setDescription($permissionData['description'] ?? null)
                            ;
                            $this->em->persist($permisson);
                            $permissions[] = $permisson;
                        }
                    }
                    $role = (new Role())
                        ->setName($roleData['name'])
                        ->setTitle($roleData['title'] ?? null)
                        ->setDescription($roleData['description'] ?? null)
                    ;
                    $rolePermissions = $role->getPermissions();
                    foreach ($permissions as $permission) {
                        $rolePermissions->add($permisson);
                    }
                    $this->em->persist($role);
                    $roles[] = $role;
                }
            }
            $user = (new User)
                ->setUsername($userData['username'])
                ->setEmail($userData['email'])
                ->setName($userData['name'] ?? null)
                ->setLastname($userData['lastname'] ?? null)
                ->setPatronymic($userData['patronymic'] ?? null)
                ->setWorkplace($userData['workplace'] ?? null)
                ->setDuty($user['duty'] ?? null)
            ;
            $password = $userData['password'];
            $encoded = $this->encoder->encodePassword($user, $password);
            $user->setPassword($encoded);
            $user->setRole($roles);
            $this->em->persist($user);
            $output->writeln('Creating user: ' . $user->getUsername() . ' ' . $user->getEmail());
        }
        try {
            $this->em->flush();
            $output->writeln('<info>success!</info>');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}