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
use Swagger\Annotations as SWG;

/**
 * @Route("/api/permission")
 * @SWG\Tag(name="permission")
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
     * @SWG\Get(
     *     summary="Список пермишнов.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешное получение."),
     * )
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
     * Удаление пермишна.
     *
     * @Route("/{id}", name="rest_api_permission_delete", methods={"DELETE"})
     *
     * @SWG\Delete(
     *     summary="Удаление пермишна.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешное удаление.")
     * )
     */
    public function delete(Permission $permission) : JsonResponse
    {
        $this->em->remove($permission);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Просмотр карточки пермишна.
     * @Route("/{id}", name="rest_api_permission_view", methods={"GET"})
     * @SWG\Get(
     *     summary="Просмотр карточки пермишна.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешное получение."),
     * )
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

    /**
     * Добавление пермишна.
     *
     * @Route("/", name="rest_api_permission_add", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *     summary="Добавление пермишна.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешно."),
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     type="string",
     *     description="Машинное имя (уникальное поле, обязательно)."
     * )
     * @SWG\Parameter(
     *     name="title",
     *     in="query",
     *     type="string",
     *     description="Название на русском языке."
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="query",
     *     type="string",
     *     description="Описание."
     * )
     */
    public function add(Request $request) : JsonResponse
    {
        $post = $request->query;
        $name = $post->get('name');
        if (!$name) {
            $message = "Required field: 'name'";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        if ($this->permissionRepository->findBy(['name' => $name])) {
            $message = "Permission with name '" . $name . "' already exists.";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        $permission = (new Permission())
            ->setName($name)
            ->setTitle(
                $post->get('title')
            )
            ->setDescription(
                $post->get('description')
            )
        ;
        $this->em->persist($permission);
        $this->em->flush();
        return new JsonResponse([
            'result' => 'ok',
            'id' => $permission->getId(),
        ]);
    }

    /**
     * Редактирование пермишна.
     *
     * @Route("/{id}", name="rest_api_permission_put", methods={"PUT"})
     *
     * @param Permission $permission
     * @return JsonResponse
     *
     * @SWG\Put(
     *     summary="Редактирование пермишна.",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(response="200", description="Успешно."),
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     type="string",
     *     description="Машинное имя."
     * )
     * @SWG\Parameter(
     *     name="title",
     *     in="query",
     *     type="string",
     *     description="Название на русском языке."
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="query",
     *     type="string",
     *     description="Описание."
     * )
     */
    public function put(Permission $permission, Request $request) : JsonResponse
    {
        $post = $request->query;
        $name = $post->get('name');
        if (!is_null($name)) {
            $permission->setName($name ?: null);
        }
        $title = $post->get('title');
        if (!is_null($title)) {
            $permission->setTitle($title ?: null);
        }
        $description = $post->get('description');
        if (!is_null($description)) {
            $permission->setDescription($description ?: null);
        }
        $this->em->persist($permission);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }
}
