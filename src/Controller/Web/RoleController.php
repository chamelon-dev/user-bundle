<?php

namespace Pantheon\UserBundle\Controller\Web;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Form\Type\RoleType;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/role")
 */
class RoleController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;
    }

    /**
     * @Route("/list", name="role_list")
     * @Template("@User/role/list.html.twig")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     */
    public function list(
        Request $request,
        RoleRepository $roleRepository,
        PaginatorInterface $paginator
    )
    {
        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ? : 1024;
        $page = $request->query->getInt('page', 1);

        $query = $roleRepository->createQueryBuilder('r')->getQuery();

        $users = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );
        return [
            'roles' => $users,
            'currentLimit' => $currentLimit,
            'limitsList' => self::PAGINATION_LIMITS,
        ];

    }

    /**
     * Редактирование роли.
     * @Route("/{id}/edit/", name="role_edit")
     * @Template("@User/role/edit.html.twig")
     *
     */
    public function edit(
        Role $role,
        Request $request
    )
    {

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            /** @var $role Role */
            $role = $form->getData();
            $this->em->persist($role);
            $this->em->flush();
            $this->addFlash('success', 'Роль сохранена.');
            $redirectUrl = (($request->query->get('fromUrl'))
                ?
                : $this->generateUrl('role_list')
            );
            return $this->redirect($redirectUrl);
        }
        return [
            'form' => $form->createView(),
            'role' => $role,
        ];

    }

    /**
     * Удаление роли.
     *
     * @Route("/{id}/delete", name="role_delete")
     */
    public function delete(
        Role $role,
        Request $request
    )
    {
        $this->em->remove($role);
        $this->em->flush();
        $this->addFlash('success', 'Роль удалена.');
        $redirectUrl = (($request->query->get('fromUrl'))
            ?
            : $this->generateUrl('role_list')
        );
        return $this->redirect($redirectUrl);
    }

    /**
     * Просмотр карточки роли.
     * @Route("/{id}/view", name="role_view")
     * @Template("@User/role/view.html.twig")
     */
    public function view(Role $role, UserRepository $userRepository)
    {
        $users = $userRepository->findWithRole($role);
        return [
            'role' => $role,
            'users' => $users,
        ];
    }
}
