<?php

namespace Pantheon\UserBundle\Command;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Добавить новые пермишны на основе аннотаций.
 * Если какой-то пермишн уже существует, пропустить.
 */
class UpdatePermissionsCommand extends Command
{
    protected static $defaultName = 'app:update-permissions';

    public function __construct(
        ContainerInterface $container,
        PermissionRepository $permissionRepository,
        EntityManagerInterface $em
    )
    {
        $this->container = $container;
        $this->permissionRepository = $permissionRepository;
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Добавить новые пермишны на основе аннотаций.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isGranteds = [];

        $annotationReader = new AnnotationReader();
        $routes = $this->container->get('router')->getRouteCollection()->all();
        $this->container->set('request', new Request(), 'request');

        foreach ($routes as $route => $param) {
            $defaults = $param->getDefaults();
            if ((isset($defaults['_controller'])) and (strpos($defaults['_controller'], '::') !== false)) {
                try {
                    list($controllerService, $controllerMethod) = explode('::', $defaults['_controller']);
                } catch (\Exception $e) {
                    // не удалось получить метод
                }

                $controllerObject = $this->container->get($controllerService);
                $reflectedMethod = new \ReflectionMethod($controllerObject, $controllerMethod);
                $annotations = $annotationReader->getMethodAnnotations($reflectedMethod);
                if ($annotations) {
                    foreach ($annotations as $annotation) {
                        if ($annotation instanceof isGranted) {
                            $isGranteds[] = $annotation->getAttributes();
                        }
                    }
                }
            }
        }
        foreach ($isGranteds as $isGranted) {
            $output->write('"' . $isGranted . '"');
            $output->write(' ... ');
            $existingPermission = $this->permissionRepository->findBy(['name' => $isGranted]);
            if ($existingPermission) {
                $output->writeln('<comment>exists</comment>');
            } else {
                $permission = (new Permission())
                    ->setName($isGranted)
                ;
                $this->em->persist($permission);
                $this->em->flush();
                $output->writeln('<info>saved</info>');
            }
        }
    }
}