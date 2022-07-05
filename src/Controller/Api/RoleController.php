<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Normalizer\RoleNormalizer;
use Pantheon\UserBundle\Repository\RoleRepository;
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
 * @Route("/api/role")
 * @OA\Tag(
 *     name="role",
 *     description="Роли",
 * )
 */
class RoleController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em,
        ResultJsonService $resultJsonService,
        RoleRepository $roleRepository
    )
    {
        $this->em = $em;
        $this->resultJsonService = $resultJsonService;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Список ролей.
     * @Route("/", name="rest_api_role_list", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/role",
     *     summary="Список ролей.",
     *     tags={"role"},
     * )
     * @OA\Response(response=200, description="OK")
     */
    public function list(
        Request $request,
        RoleRepository $roleRepository,
        PaginatorInterface $paginator
    ) : JsonResponse
    {
        $serializer = new Serializer(
            [new RoleNormalizer()],
            [new JsonEncoder()]
        );
        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ? : 1024;
        $page = $request->query->getInt('page', 1);

        $query = $roleRepository->createQueryBuilder('r')->getQuery();

        $roles = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );
        $result = [];
        foreach ($roles->getItems() as $item) {
            $result[] = $serializer->normalize($item);
        }
        return new JsonResponse($result);
    }

    /**
     * Удаление роли.
     *
     * @Route("/{id}", name="rest_api_role_delete", methods={"DELETE"})
     *
     * @OA\Delete(
     *     path="/api/role/{id}",
     *     summary="Удаление роли.",
     *     tags={"role"},
     *     @OA\Parameter(
     *         description="Id роли.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         @OA\Examples(example="result", value={"result": "ok"}, summary="Успешное удаление"),
     *     )
     * )
     */
    public function delete(Role $role)
    {
        $this->em->remove($role);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Просмотр карточки роли.
     *
     * @Route("/{id}", name="rest_api_role_view", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/role/{id}",
     *     summary="Просмотр карточки роли.",
     *     tags={"role"},
     *     @OA\Parameter(
     *         description="Id роли.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     )
     * )
     * @OA\Response(response=200, description="OK")
     */
    public function view(Role $role) : JsonResponse
    {
        $serializer = new Serializer(
            [new RoleNormalizer()],
            [new JsonEncoder()]
        );
        $result = $serializer->normalize($role);
        return new JsonResponse($result);
    }

    /**
     * Добавление новой роли.
     *
     * @Route("/", name="rest_api_role_add", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/role",
     *     summary="Добавление новой роли.",
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Машинное имя (уникальное поле)."
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Название на русском языке."
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Описание."
     *     ),
     * )
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         @OA\Examples(
     *              example="ok",
     *              value={"result": "ok", "id": "b24cbf72-e458-4c5d-a64e-02355814f6df"},
     *              summary="Успешное создание",
     *         ),
     *         @OA\Examples(
     *              example="error",
     *              value={"result": "error", "message": "Role with name 'ROLE_USER' already exists"},
     *              summary="Ошибка",
     *          ),
     *     )
     * )
     */
    public function add(Request $request) : JsonResponse
    {
        $post = $request->request;
        $name = $post->get('name');
        if (!$name) {
            $message = "Required field: 'name'";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        if ($this->roleRepository->findBy(['name' => $name])) {
            $message = "Role with name '" . $name . "' already exists.";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        $role = (new Role())
            ->setName($name)
            ->setTitle(
                $post->get('title')
            )
            ->setDescription(
                $post->get('description')
            )
        ;
        $this->em->persist($role);
        $this->em->flush();
        return new JsonResponse([
            'result' => 'ok',
            'id' => $role->getId(),
        ]);
    }

    /**
     * Редактирование роли.
     *
     * @Route("/{id}", name="rest_api_role_put", methods={"PUT"})
     *
     * @param Role $role
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/role/{id}",
     *     summary="Редактирование роли.",
     *     tags={"role"},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Машинное имя (уникальное поле)."
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Название на русском языке."
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Описание."
     *     ),
     * )
     * @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         @OA\Examples(example="ok", value={"result": "ok"}, summary="Успешно."),
     *     )
     * )
     */
    public function put(Role $role, Request $request) : JsonResponse
    {
        $post = $request->request;
        $name = $post->get('name');
        if (!is_null($name)) {
            $role->setName($name ?: null);
        }
        $title = $post->get('title');
        if (!is_null($title)) {
            $role->setTitle($title ?: null);
        }
        $description = $post->get('description');
        if (!is_null($description)) {
            $role->setDescription($description ?: null);
        }
        $this->em->persist($role);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }
}
