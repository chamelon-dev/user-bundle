<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Normalizer\PermissionNormalizer;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Pantheon\UserBundle\Service\StringService;
use Pantheon\UserBundle\Service\UserRightsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Контроллер для связи пермишнов с ролями.
 *
 * @Route("/api/role")
 */
class RolePermissionController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $em,
        ResultJsonService $resultJsonService,
        UserRepository $userRepository,
        UserRightsService $userRightsService,
        StringService $stringService,
        RoleRepository $roleRepository,
        PermissionRepository $permissionRepository
    )
    {
        $this->em = $em;
        $this->resultJsonService = $resultJsonService;
        $this->userRepository = $userRepository;
        $this->userRightsService = $userRightsService;
        $this->stringService = $stringService;
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Привязать пермишн(ы) к роли.
     * Если такой пермишн уже есть, ничего не происходит.
     * Можно привязывать в пакетном режиме.
     *
     * @Route("/{id}/permission/{permission}/", name="rest_api_role_permission_add", methods={"POST"})
     *
     * @param Role $role
     * @param string $permission Id пермишна или Json-массив со списком id;
     * @return JsonResponse
     */
    public function add(Role $role, string $permission) : JsonResponse
    {
        $permissions = (($this->stringService->isJson($permission))
            ? json_decode($permission)
            : [$permission]
        );
        foreach ($permissions as $permissionId) {
            $permission = $this->permissionRepository->find($permissionId);
            if ($permission) {
                $rolePermissions = $role->getPermissions();
                if (!$rolePermissions->contains($permission)) {
                    $rolePermissions->add($permission);
                }
            } else {
                return new JsonResponse($this->resultJsonService->error('Permission with id ' . $permissionId . ' not found.'));
            }
        }
        $this->em->persist($role);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Отвязать пермишн от роли.
     * Можно в пакетном режиме.
     *
     * @Route("/{id}/permission/{permission}/", name="rest_api_role_permission_delete", methods={"DELETE"})
     *
     * @param Role $role
     * @param string $permission Id пермишна или Json-массив со списком id;
     * @return JsonResponse
     */
    public function delete(Role $role, string $permission) : JsonResponse
    {
        $permissions = (($this->stringService->isJson($permission))
            ? json_decode($permission)
            : [$permission]
        );
        foreach ($permissions as $permissionId) {
            $permission = $this->permissionRepository->find($permissionId);
            if ($permission) {
                $rolePermissions = $role->getPermissions();
                if ($rolePermissions->contains($permission)) {
                    $rolePermissions->removeElement($permission);
                }
            } else {
                return new JsonResponse($this->resultJsonService->error('Permission with id ' . $permissionId . ' not found.'));
            }
        }
        $this->em->persist($role);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Список пермишнов, привязанных к роли.
     *
     * @Route("/{id}/permission/", name="rest_api_role_permission_list", methods={"GET"})
     *
     * @param Role $role
     */
    public function list(Role $role) : JsonResponse
    {
        $serializer = new Serializer(
            [new PermissionNormalizer()],
            [new JsonEncoder()]
        );
        $permissions = $role->getPermissions()->toArray();
        $result = [];
        if ($permissions) {
            foreach ($permissions as $permission) {
                $result[] = $serializer->normalize($permission);
            }
        }
        return new JsonResponse($result);
    }
}
