<?php

namespace Pantheon\UserBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EnvExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('env', [$this, 'getEnvironmentVariable']),
        ];
    }

    /**
     * @param String $varname
     * @return String
     */
    public function getEnvironmentVariable(string $varname) : ?string
    {
        return $_ENV[$varname] ?? null;
    }
}