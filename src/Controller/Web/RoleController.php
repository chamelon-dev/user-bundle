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
     * @Route("/add/", name="role_add")
     */
    public function add()
    {


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

    /**
     * Создание тестовой роли с пермишном.
     * TODO: удалить
     *
     * @Route("/init", name="role_init")
     */
    public function init() : Response
    {
        $permission = (new Permission())
            ->setName('can everything')
            ->setTitle('может все')
        ;
        $permission2 = (new Permission())
            ->setName('can something else')
            ->setTitle('может кое-что еще')
        ;
        $permission3 = (new Permission())
            ->setName('can view')
            ->setTitle('может смотреть')
        ;
        $permission4 = (new Permission())
            ->setName('can edit')
            ->setTitle('может редактировать')
        ;
        $permission5 = (new Permission())
            ->setName('can nothing')
            ->setTitle('ничего не может')
        ;

        $this->em->persist($permission);
        $this->em->persist($permission2);
        $this->em->persist($permission3);
        $this->em->persist($permission4);
        $this->em->persist($permission5);
        $role = (new Role())
            ->setName('SUPER_ADMIN')
            ->setTitle('супер админ')
            ->addPermission($permission)
            ->addPermission($permission2)
        ;
        $role2 = (new Role())
            ->setName('OBSERVER')
            ->setTitle('наблюдатель')
            ->addPermission($permission2)
            ->addPermission($permission3)
        ;
        $role3 = (new Role())
            ->setName('WEAK_USER')
            ->setTitle('слабый пользователь')
            ->addPermission($permission5)
        ;
        $role4 = (new Role())
            ->setName('EDITOR')
            ->setTitle('редактор')
            ->addPermission($permission2)
            ->addPermission($permission3)
            ->addPermission($permission4)
        ;


        try {
            $this->em->persist($role);
            $this->em->persist($role2);
            $this->em->persist($role3);
            $this->em->persist($role4);
            $this->em->flush();
            $message = 'Создана роль <b>' . $role->getName() . '</b>';
        } catch (\Exception $e) {
            $message = '<b>Создать роль не удалось.</b><br>' . $e->getMessage();
        }
        return new Response($message);
    }
}
