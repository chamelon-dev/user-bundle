<?php


namespace Pantheon\UserBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * @Route("/login", name="user_login")
     * @param AuthenticationUtils $authenticationUtils
     * @Template("@User/security/login.html.twig")
     * @return array
     */
    public function login(AuthenticationUtils $authenticationUtils): array
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return ['last_username' => $lastUsername, 'error' => $error];
    }

    /**
     * @Route("/login_check", name="user_check")
     *
     * @return array
     */
    public function check(): array
    {
        return [

        ];
    }

    /**
     * @Route("/logout/success", name="user.logout")
     * @Template("@User/security/logout.html.twig")
     * @return RedirectResponse
     */
    public function logout()
    {

        return new RedirectResponse('/');
    }

}