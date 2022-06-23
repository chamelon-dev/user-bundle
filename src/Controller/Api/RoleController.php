<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Normalizer\RoleNormalizer;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Util\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/role")
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
     *
     * @Route("/list", name="api_role_list", methods={"GET"})
     * @Route("/", name="rest_api_role_list", methods={"GET"})
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
     * @Route("/{id}/delete", name="api_role_delete", methods={"GET"})
     * @Route("/{id}", name="rest_api_role_delete", methods={"DELETE"})
     */
    public function delete(Role $role)
    {
        $this->em->remove($role);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }

    /**
     * Просмотр карточки роли.
     * @Route("/{id}/view", name="api_role_view", methods={"GET"})
     * @Route("/{id}", name="rest_api_role_view", methods={"GET"})
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
     * @Route("/add", name="api_role_add", methods={"POST"})
     * @Route("/", name="rest_api_role_add", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request) : JsonResponse
    {
        $post = $request->query;
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
     */
    public function put(Role $role, Request $request) : JsonResponse
    {
        $post = $request->query;
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
