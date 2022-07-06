<?php

namespace Pantheon\UserBundle\Controller;

use Pantheon\UserBundle\Entity\User;
use Pantheon\UserBundle\Repository\UserRepository;
use Pantheon\UserBundle\UserBundle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class TestController extends AbstractController
{
    /**
     * @Route("/profile", name="user.profile")
     */
    public function profile()
    {
        return $this->redirectToRoute('user_list');
    }
}
