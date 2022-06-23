<?php

namespace Pantheon\UserBundle\Controller\Web;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Form\Type\PermissionType;
use Pantheon\UserBundle\Repository\PermissionRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/permission")
 */
class PermissionController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    /**
     * @Route("/list", name="permission_list")
     * @Template("@User/permission/list.html.twig")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     */


    public function list(
        Request              $request,
        PermissionRepository $permissionRepository,
        PaginatorInterface   $paginator
    )
    {
        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ?: 1024;
        $page = $request->query->getInt('page', 1);

        $query = $permissionRepository->createQueryBuilder('p')->getQuery();

        $permissions = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );
        return [
            'permissions' => $permissions,
            'currentLimit' => $currentLimit,
            'limitsList' => self::PAGINATION_LIMITS,
        ];
    }

    /**
     * Добавление нового пермишна.
     * @Route("/add/", name="permission_add")
     */
    public function add()
    {

    }

    /**
     * Редактирование пермишна.
     * @Route("/{id}/edit/", name="permission_edit")
     * @Template("@User/permission/edit.html.twig")
     *
     */
    public function edit(
        Permission $permission,
        Request $request
    )
    {
        $form = $this->createForm(PermissionType::class, $permission);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            /** @var $permission Permission */
            $permission = $form->getData();
            $this->em->persist($permission);
            $this->em->flush();
            $this->addFlash('success', 'Пермишн сохранен.');
            $redirectUrl = (($request->query->get('fromUrl'))
                ?
                : $this->generateUrl('role_list')
            );
            return $this->redirect($redirectUrl);
        }

        return [
            'form' => $form->createView(),
            'permission' => $permission,
        ];

    }

    /**
     * Удаление пермишна.
     *
     * @Route("/{id}/delete", name="permission_delete")
     */
    public function delete(
        Permission $permission,
        Request    $request
    )
    {
        $this->em->remove($permission);
        $this->em->flush();
        $this->addFlash('success', 'Пермишн удален.');
        $redirectUrl = (($request->query->get('fromUrl'))
            ?: $this->generateUrl('permission_list')
        );
        return $this->redirect($redirectUrl);
    }

    /**
     * Просмотр карточки пермишна.
     * @Route("/{id}/view", name="permission_view")
     * @Template("@User/permission/view.html.twig")
     */
    public function view(Permission $permission)
    {
        return [
            'permission' => $permission,
        ];
   }
}
