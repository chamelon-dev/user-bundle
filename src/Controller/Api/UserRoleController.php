<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Normalizer\RoleNormalizer;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Pantheon\UserBundle\Service\StringService;
use Pantheon\UserBundle\Service\UserRightsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Swagger\Annotations as SWG;

/**
 * Контроллер для связи ролей с пользователем.
 *
 * @Route("/api/user")
 * @SWG\Tag(name="user_role")
 */
class UserRoleController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        ResultJsonService $resultJsonService,
        UserRepository $userRepository,
        UserRightsService $userRightsService,
        StringService $stringService,
        RoleRepository $roleRepository
    )
    {
        $this->em = $em;
        $this->userPasswordEncoder = $encoder;
        $this->resultJsonService = $resultJsonService;
        $this->userRepository = $userRepository;
        $this->userRightsService = $userRightsService;
        $this->stringService = $stringService;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Привязать роль к пользователю.
     * Если у пользователя такая роль уже есть, ничего не происходит.
     * Можно привязывать в пакетном режиме.
     *
     * @Route("/{id}/role/{role}/", name="rest_api_user_role_add", methods={"POST"})
     *
     * @param User $user
     * @param string $role Id роли или Json-массив со списком id;
     * @return JsonResponse
     *
     * @SWG\Post(
     *     summary="Привязать роль к пользователю.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешно."),
     * )
     */
    public function add(User $user, string $role) : JsonResponse
    {
        $roles = (($this->stringService->isJson($role))
            ? json_decode($role)
            : [$role]
        );
        foreach ($roles as $roleId) {
            $role = $this->roleRepository->find($roleId);
            if ($role) {
                $userRoles = $user->getRole();
                if (!$userRoles->contains($role)) {
                    $userRoles->add($role);
                }
            } else {
                return new JsonResponse($this->resultJsonService->error('Role with id ' . $roleId . ' not found.'));
            }
        }
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Отвязать роль от пользователя.
     * Можно в пакетном режиме.
     *
     * @Route("/{id}/role/{role}/", name="rest_api_user_role_delete", methods={"DELETE"})
     *
     * @param User $user
     * @param string $role Id роли или Json-массив со списком id;
     * @return JsonResponse
     *
     * @SWG\Delete(
     *     summary="Отвязать роль от пользователя.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешно."),
     * )
     */
    public function delete(User $user, string $role) : JsonResponse
    {
        $roles = (($this->stringService->isJson($role))
            ? json_decode($role)
            : [$role]
        );
        foreach ($roles as $roleId) {
            $role = $this->roleRepository->find($roleId);
            if ($role) {
                $userRoles = $user->getRole();
                if ($userRoles->contains($role)) {
                    $userRoles->removeElement($role);
                }
            } else {
                return new JsonResponse($this->resultJsonService->error('Role with id ' . $roleId . ' not found.'));
            }
        }
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Список ролей, привязанных к пользователю.
     *
     * @Route("/{id}/role/", name="rest_api_user_role_list", methods={"GET"})
     *
     * @param User $user
     *
     * @SWG\Get(
     *     summary="Список ролей, привязанных к пользователю.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешное получение."),
     * )
     */
    public function list(User $user) : JsonResponse
    {
        $serializer = new Serializer(
            [new RoleNormalizer()],
            [new JsonEncoder()]
        );
        $roles = $this->userRightsService->getRoles($user);
        $result = [];
        if ($roles) {
            foreach ($roles as $role) {
                $result[] = $serializer->normalize($role);
            }
        }
        return new JsonResponse($result);
    }
}
