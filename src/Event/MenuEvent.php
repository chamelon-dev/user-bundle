<?php

namespace Pantheon\UserBundle\Event;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class MenuEvent
{
    public const NAME = 'app.menu';

    private $factory;
    private $menu;
    private $options;

    /**
     * @param FactoryInterface $factory
     * @param ItemInterface $menu
     * @param array $options
     */
    public function __construct(FactoryInterface $factory, ItemInterface $menu, array $options)
    {
        $this->factory = $factory;
        $this->menu = $menu;
        $this->options = $options;
    }

    /**
     * @return FactoryInterface
     */
    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return ItemInterface
     */
    public function getMenu(): ItemInterface
    {
        return $this->menu;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
