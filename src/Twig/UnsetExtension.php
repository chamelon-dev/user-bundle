<?php

namespace Pantheon\UserBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UnsetExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('unset', [$this, 'unset']),
        ];
    }

    public function unset($array, $key)
    {
        if (isset($array[$key])) {
            unset($array[$key]);
        }
        return $array;
    }
}