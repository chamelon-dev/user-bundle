<?php

namespace Pantheon\UserBundle\Event\Listener\Menu;

use Pantheon\UserBundle\Event\MenuEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Меню в списке пользователей.
 */
class UserListener
{
    private $security;
    private $requestStack;

    public function __construct(RequestStack $requestStack, Security $security)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    /**
     * @param MenuEvent $event
     */
    public function onMenu(MenuEvent $event)
    {
        $options = $event->getOptions();
        $id = $options['id'] ?? null;
        $menu = $event->getMenu();

        $menu->addChild('Пользователи', ['route' => 'user_list']);
        $menu->addChild('Роли', ['route' => 'role_list']);
        $menu->addChild('Пермишны', ['route' => 'permission_list']);
    }
}
