<?php

namespace Pantheon\UserBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api")
 */
class LoginController extends AbstractController
{
    public function __construct(
        Security $security
    )
    {
        $this->security = $security;
    }

//    /**
//     * @Route("/logout", name="api_logout", methods={"GET"})
//     */
//    public function logout() : JsonResponse
//    {
//        return new JsonResponse([
//            'id' => $this->security->getUser()->getId(),
//            'username' => $this->security->getUser()->getUsername(),
//        ]);
//    }
}
