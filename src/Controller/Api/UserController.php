<?php

namespace Pantheon\UserBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Normalizer\PermissionNormalizer;
use Pantheon\UserBundle\Normalizer\RoleNormalizer;
use Pantheon\UserBundle\Normalizer\UserNormalizer;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\Service\ResultJsonService;
use Pantheon\UserBundle\Service\UserRightsService;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/user")
 * @OA\Tag(
 *     name="user",
 *     description="Пользователи",
 * )
 */
class UserController extends AbstractController
{
    const PAGINATION_LIMITS = [10, 30, 120];

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        ResultJsonService $resultJsonService,
        UserRepository $userRepository,
        LoggerInterface $appLogger
    )
    {
        $this->em = $em;
        $this->userPasswordEncoder = $encoder;
        $this->resultJsonService = $resultJsonService;
        $this->userRepository = $userRepository;
        $this->logger = $appLogger;
    }

    /**
     * @Route("/", name="rest_api_user_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserRepository $userRepository
     *
     * @OA\Get(
     *     path="/api/user",
     *     summary="Список пользователей.",
     *     tags={"user"},
     * )
     * @OA\Response(response=200, description="OK")
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
     * @Route("/{id}", name="rest_api_user_delete", methods={"DELETE"})
     *
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     summary="Удаление пользователя.",
     *     tags={"user"},
     *     @OA\Parameter(
     *         description="Id пользователя.",
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
    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse($this->resultJsonService->ok(), Response::HTTP_OK);
    }

    /**
     * Просмотр карточки пользователя.
     *
     * @Route("/{id}", name="rest_api_user_view", methods={"GET"})
     *
     * @OA\Get(
     *     path="/api/user/{id}",
     *     summary="Просмотр карточки пользователя.",
     *     tags={"user"},
     *     @OA\Parameter(
     *         description="Id пользователя.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string"),
     *     )
     * )
     * @OA\Response(response=200, description="OK")
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
     * @Route("/", name="rest_api_user_add", methods={"POST"})
     * @Route("", methods={"POST"})
     *
     * @OA\Post(
     *     path="/api/user",
     *     summary="Добавление нового пользователя.",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="username",
     *         in="path",
     *         @OA\Schema(type="string"),
     *         description="Логин пользователя (уникальное поле)."
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         @OA\Schema(type="string"),
     *         description="E-mail пользователя (уникальное поле)."
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Пароль пользователя в не закодированном виде."
     *     ),
     *     @OA\Parameter(
     *         name="lastname",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Фамилия."
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Имя."
     *     ),
     *     @OA\Parameter(
     *         name="patronymic",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Отчество."
     *     ),
     *     @OA\Parameter(
     *         name="birthdate",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Дата рождения в формате ДДДД-ММ-ГГ."
     *     ),
     *     @OA\Parameter(
     *         name="workplace",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Место работы."
     *     ),
     *     @OA\Parameter(
     *         name="duty",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Должность."
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Номер телефона."
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
     *              value={"result": "error", "message": "Required fields: 'username', 'email', 'password'"},
     *              summary="Ошибка",
     *          ),
     *     )
     * )
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder) : JsonResponse
    {
        $post = $request->request;
        $this->logger->info('API USER CREATE', ['POST RECEIVED' => $post->all()]);
        $username = $post->get('username');
        $email = $post->get('email');
        $password = $post->get('password');
        if (!$username or !$email or !$password) {
            $message = "Required fields: 'username', 'email', 'password'";
            $errorArray = $this->resultJsonService->error($message);
            $this->logger->error('API USER CREATE', ['error' => $errorArray]);
            return new JsonResponse($errorArray);
        }
        $birthdate = null;
        if ($birthday = $post->get('birthdate')) {
            try {
                $birthdate = new \DateTime($birthday);
            } catch (\Exception $e) {
                $message = "Can not parse date '" . $birthday . "'";
                $errorArray = $this->resultJsonService->error($message);
                $this->logger->error('API USER CREATE', ['error' => $errorArray]);
                return new JsonResponse($errorArray);
            }
        }
        if ($this->userRepository->findBy(['username' => $username])) {
            $message = "User with username '" . $username . "' already exists.";
            $errorArray = $this->resultJsonService->error($message);
            $this->logger->error('API USER CREATE', ['error' => $errorArray]);
            return new JsonResponse($errorArray);
        }
        if ($this->userRepository->findBy(['email' => $email])) {
            $message = "User with email '" . $email . "' already exists.";
            $errorArray = $this->resultJsonService->error($message);
            $this->logger->error('API USER CREATE', ['error' => $errorArray]);
            return new JsonResponse($errorArray);
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
        $okArray = [
            'result' => 'ok',
            'id' => $user->getId(),
        ];
        $this->logger->info('API USER CREATE', ['ok' => $okArray]);
        return new JsonResponse($okArray);
    }

    /**
     * Редактирование пользователя.
     *
     * @Route("/{id}", name="rest_api_user_put", methods={"PUT"})
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/user/{id}",
     *     summary="Редактирование пользователя.",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Id пользователя."
     *     ),
     *     @OA\Parameter(
     *         name="lastname",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Фамилия."
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Имя."
     *     ),
     *     @OA\Parameter(
     *         name="patronymic",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Отчество."
     *     ),
     *     @OA\Parameter(
     *         name="birthdate",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Дата рождения в формате ДДДД-ММ-ГГ."
     *     ),
     *     @OA\Parameter(
     *         name="workplace",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Место работы."
     *     ),
     *     @OA\Parameter(
     *         name="duty",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Должность."
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         @OA\Schema(type="string"),
     *         description="Номер телефона."
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
    public function put(User $user, Request $request) : JsonResponse
    {
        $post = $request->request;
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
