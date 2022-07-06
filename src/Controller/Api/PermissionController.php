<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Normalizer\PermissionNormalizer;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/permission")
 * @OA\Tag(
 *     name="permission",
 *     description="Пермишны",
 * )
 */
class PermissionController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em,
        ResultJsonService $resultJsonService,
        PermissionRepository $permissionRepository

    )
    {
        $this->em = $em;
        $this->resultJsonService = $resultJsonService;
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Список пермишнов.
     *
     * @Route("/", name="rest_api_permission_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserRepository $userRepository
     *
     * @OA\Get(
     *     path="/api/permissons",
     *     summary="Список пермишнов.",
     *     tags={"permission"},
     * )
     * @OA\Response(response=200, description="OK")
     *
     */
    public function list(
        Request $request,
        PermissionRepository $permissionRepository,
        PaginatorInterface $paginator
    ) : JsonResponse
    {
        $serializer = new Serializer(
            [new PermissionNormalizer()],
            [new JsonEncoder()]
        );

        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ?: 1024;
        $page = $request->query->getInt('page', 1);

        $query = $permissionRepository->createQueryBuilder('p')->getQuery();

        $permissions = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );

        $result = [];
        foreach ($permissions->getItems() as $item) {
            $result[] = $serializer->normalize($item);
        }
        return new JsonResponse($result);
    }

    /**
     * Просмотр карточки пермишна.
     *
     * @Route("/{id}", name="rest_api_permission_view", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/permission/{id}",
     *     summary="Просмотр карточки пермишна.",
     *     tags={"permission"},
     *     @OA\Parameter(
     *         description="Id пермишна.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     )
     * )
     * @OA\Response(response=200, description="OK")
     */
    public function view(Permission $permission) : JsonResponse
    {
        $serializer = new Serializer(
            [new PermissionNormalizer()],
            [new JsonEncoder()]
        );
        $result = $serializer->normalize($permission);
        return new JsonResponse($result);
    }
}
