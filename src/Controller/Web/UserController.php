<?php

namespace Pantheon\UserBundle\Controller\Web;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Form\Type\UserType;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\UserRightsService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->em = $em;
        $this->userPasswordEncoder = $encoder;
    }

    /**
     * @Route("/list", name="user_list")
     * @Template("@User/user/list.html.twig")
     *
     * @param Request $request
     * @param UserRepository $userRepository
     */
    public function list(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator
    )
    {
        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ? : 1024;
        $page = $request->query->getInt('page', 1);

        $query = $userRepository->createQueryBuilder('u')->getQuery();

        $users = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );
        return [
            'users' => $users,
            'currentLimit' => $currentLimit,
            'limitsList' => self::PAGINATION_LIMITS,
        ];

    }

    /**
     * Добавление нового пользователя.
     * @Route("/add/", name="user_add")
     */
    public function add()
    {


    }

    /**
     * Редактирование пользователя.
     * @Route("/{id}/edit/", name="user_edit")
     * @Template("@User/user/edit.html.twig")
     *
     */
    public function edit(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()) {
            /** @var $user User */
            $user = $form->getData();
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'Пользователь сохранен.');
            $redirectUrl = (($request->query->get('fromUrl'))
                ?
                : $this->generateUrl('user_list')
            );
            return $this->redirect($redirectUrl);
        }
        return [
            'form' => $form->createView(),
            'user' => $user,
        ];
    }

    /**
     * Удаление пользователя.
     *
     * @Route("/{id}/delete", name="user_delete")
     */
    public function delete(
        User $user,
        Request $request
    )
    {
        // event
        $this->em->remove($user);
        $this->em->flush();
        $this->addFlash('success', 'Пользователь удален.');
        $redirectUrl = (($request->query->get('fromUrl'))
            ?
            : $this->generateUrl('user_list')
        );
        return $this->redirect($redirectUrl);
    }

    /**
     * Просмотр карточки пользователя.
     * @Route("/{id}/view", name="user_view")
     * @Template("@User/user/view.html.twig")
     */
    public function view(User $user, UserRightsService $userRightsService)
    {

        $rolesValues = $userRightsService->getRolesValues($user);
        $permissionsValues = $userRightsService->getPermissionsValues($user);

        return [
            'user' => $user,
            'roles' => $rolesValues,
            'permissions' => $permissionsValues,
        ];


    }


    /**
     * @Route("/test", name="usertest")
     */
    public function test(Request $request)
    {
        return new Response('ok!!!');
    }

    /**
     * Создание тестового пользователя.
     * TODO: удалить
     *
     * @Route("/init", name="user_init")
     */
    public function init() : Response
    {
        $user = new User();
        $user
            ->setUsername('bsa')
            ->setEmail('b-s-a@mail.ru')
            ->setPassword('kek')
        ;
        try {
            $this->em->persist($user);
            $this->em->flush();
            $message = 'Создан пользователь <b>' . $user->getUsername() . '</b>';
        } catch (\Exception $e) {
            $message = '<b>Пользователя создать не удалось.</b><br>' . $e->getMessage();
        }
        return new Response($message);
    }

    /**
     * Создание нескольких тестовых пользователей.
     * TODO: удалить
     *
     * @Route("/init/{count}", name="user_init_count")
     */
    public function initcount(int $count) : Response
    {
        $good = 0;
        $bad = 0;
        $words = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
        $colors = ['red', 'green', 'blue', 'rose', 'purple', 'yellow', 'pink', 'aquamarine'];
        for ($i = 0; $i < $count; $i++) {
            $word = $words[array_rand($words)];
            $color = $colors[array_rand($colors)];
            $user = (new User())
                ->setUsername('robot' . $color . $word . $i)
                ->setEmail('b-s-a' . rand(10000, 99999) . '@mail.ru')
            ;
            $plainPassword = 'robot' . $i;
            $password = $this->userPasswordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($password);
            try {
                $this->em->persist($user);
                $this->em->flush();
                $good++;
                $message = 'Создан пользователь <b>' . $user->getUsername() . '</b>';
            } catch (\Exception $e) {
                $bad++;
                $message = '<b>Пользователя создать не удалось.</b><br>' . $e->getMessage();
            }
        }
        return new Response($good . ' пользователей создано');
    }


    /**
     * @Route("/bsa", name="test_bsa")
     */
    public function bsa(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $bsa = $userRepository->find('b916f6cf-335c-4335-a13a-93f2737e2547');
        $superAdminRole = $roleRepository->findBy(['name' => 'SUPER_ADMIN'])[0];

        /** @var PersistentCollection $bsaRoles */
        $bsaRoles = $bsa->getRole();
        die();

        /** @var PersistentCollection $permissions */
        $permissions = $superAdminRole->getPermissions();
        dump($permissions->getValues());
        die();
//        $bsa->setRoles([
//            'SUPER_ADMIN'
//        ]);
//        $this->em->persist($bsa);
//        $this->em->flush();
        dump($bsa, $bsa->getRoles());
        die();
    }

}
