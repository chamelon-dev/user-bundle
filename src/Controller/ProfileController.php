<?php

namespace Pantheon\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="user.profile")
     */
    public function profile()
    {
        return $this->redirectToRoute('user_list');
    }
}
