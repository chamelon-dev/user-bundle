<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Form\Type\UserType;
use Pantheon\UserBundle\Normalizer\PermissionNormalizer;
use Pantheon\UserBundle\Normalizer\RoleNormalizer;
use Pantheon\UserBundle\Normalizer\UserNormalizer;
use Pantheon\UserBundle\Repository\RoleRepository;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Pantheon\UserBundle\Service\UserRightsService;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Util\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/api/user")
 */
class UserController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        ResultJsonService $resultJsonService,
        UserRepository $userRepository
    )
    {
        $this->em = $em;
        $this->userPasswordEncoder = $encoder;
        $this->resultJsonService = $resultJsonService;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/list", name="api_user_list", methods={"GET"})
     * @Route("/", name="rest_api_user_list", methods={"GET"})
     * @param Request $request
     * @param UserRepository $userRepository
     */
    public function list(
        Request $request,
        UserRepository $userRepository,
        PaginatorInterface $paginator
    ) : JsonResponse
    {
        $serializer = new Serializer(
            [new UserNormalizer()],
            [new JsonEncoder()]
        );
        $currentLimit = (int)$request->query->get('limit', self::PAGINATION_LIMITS[0]) ? : 1024;
        $page = $request->query->getInt('page', 1);

        $query = $userRepository->createQueryBuilder('u')->getQuery();

        $users = $paginator->paginate(
            $query,
            $page,
            $currentLimit,
            []
        );
        $result = [];
        foreach ($users->getItems() as $item) {
            $result[] = $serializer->normalize($item);
        }
        return new JsonResponse($result);
    }

    /**
     * Удаление пользователя.
     *
     * @Route("/{id}/delete", name="api_user_delete", methods={"GET"})
     * @Route("/{id}", name="rest_api_user_delete", methods={"DELETE"})
     */
    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok(), Response::HTTP_OK);
    }

    /**
     * Просмотр карточки пользователя.
     * @Route("/{id}/view", name="api_user_view", methods={"GET"})
     * @Route("/{id}", name="rest_api_user_view", methods={"GET"})
     */
    public function view(User $user, UserRightsService $userRightsService) : JsonResponse
    {
        $serializer = new Serializer(
            [new UserNormalizer(), new RoleNormalizer(), new PermissionNormalizer()],
            [new JsonEncoder()]
        );
        $result = $serializer->normalize($user);
        $roles = $userRightsService->getRoles($user);
        if ($roles) {
            foreach ($roles as $role) {
                $result['roles'][] = $serializer->normalize($role);
            }
        }
        return new JsonResponse($result);
    }

    /**
     * Добавление нового пользователя.
     *
     * @Route("/add", name="api_user_add", methods={"POST"})
     * @Route("/", name="rest_api_user_add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder) : JsonResponse
    {
        $post = $request->query;
        $username = $post->get('username');
        $email = $post->get('email');
        $password = $post->get('password');
        if (!$username or !$email or !$password) {
            $message = "Required fields: 'username', 'email', 'password'";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        $birthdate = null;
        if ($birthday = $post->get('birthdate')) {
            try {
                $birthdate = new \DateTime($birthday);
            } catch (\Exception $e) {
                $message = "Can not parse date '" . $birthday . "'";
                return new JsonResponse($this->resultJsonService->error($message));
            }
        }
        if ($this->userRepository->findBy(['username' => $username])) {
            $message = "User with username '" . $username . "' already exists.";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        if ($this->userRepository->findBy(['email' => $email])) {
            $message = "User with email '" . $email . "' already exists.";
            return new JsonResponse($this->resultJsonService->error($message));
        }
        $user = (new User)
            ->setUsername($username)
            ->setEmail($email)
            ->setName(
                $post->get('name')
            )
            ->setLastname(
                $post->get('lastname')
            )
            ->setPatronymic(
                $post->get('patronymic')
            )
            ->setWorkplace(
                $post->get('workplace')
            )
            ->setDuty(
                $post->get('duty')
            )
            ->setPhone(
                $post->get('phone')
            )
            ->setBirthdate(
                $birthdate
            )
       ;
        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse([
            'result' => 'ok',
            'id' => $user->getId(),
        ]);
    }


    /**
     * Редактирование пользователя.
     *
     * @Route("/{id}", name="rest_api_user_put", methods={"PUT"})
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function put(User $user, Request $request) : JsonResponse
    {
        $post = $request->query;
        $name = $post->get('name');
        if (!is_null($name)) {
            $user->setName($name ?: null);
        }
        $lastname = $post->get('lastname');
        if (!is_null($lastname)) {
            $user->setLastname($lastname ?: null);
        }
        $patronymic = $post->get('patronymic');
        if (!is_null($patronymic)) {
            $user->setPatronymic($patronymic ?: null);
        }
        $workplace = $post->get('workplace');
        if (!is_null($workplace)) {
            $user->setWorkplace($workplace ?: null);
        }
        $duty = $post->get('duty');
        if (!is_null($duty)) {
            $user->setDuty($duty ?: null);
        }
        $phone = $post->get('phone');
        if (!is_null($phone)) {
            $user->setPhone($phone ?: null);
        }
        $birthday = $post->get('birthdate');
        if (!is_null($birthday)) {
            if ($birthday) {
                try {
                    $birthdate = new \DateTime($birthday);
                } catch (\Exception $e) {
                    $message = "Can not parse date '" . $birthday . "'";
                    return new JsonResponse($this->resultJsonService->error($message));
                }
                $user->setBirthdate($birthdate);
            } else {
                $user->setBirthdate(null);
            }
        }
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok());
    }
}
