<?php

namespace Pantheon\UserBundle\Provider;

use InvalidArgumentException;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Pantheon\UserBundle\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package App\Menu
 */
class MenuProvider implements MenuProviderInterface
{
    private $factory;
    private $dispatcher;

    /**
     * @param FactoryInterface $factory
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(FactoryInterface $factory, EventDispatcherInterface $dispatcher)
    {
        $this->factory = $factory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $name
     * @param array $options
     * @return ItemInterface
     * @throws InvalidArgumentException if the menu does not exists
     */
    public function get(string $name, array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $this->dispatcher->dispatch(
            new MenuEvent($this->factory, $menu, $options),
            MenuEvent::NAME . '.' . $name
        );
        return $menu;
    }

    /**
     * @param string $name
     * @param array $options
     * @return bool
     */
    public function has(string $name, array $options = []): bool
    {
        return !empty(trim($name));
    }
}
