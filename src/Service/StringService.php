<?php

namespace Pantheon\UserBundle\Service;

class StringService
{
    /**
     * Является ли строка JSON.
     *
     * @param string $string
     * @return bool
     */
    function isJson(string $string) : bool
    {
        if (ctype_digit($string)) {
            return false;
        }
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Объединить параметры в путь, убрав дублирующиеся слеши.
     * Результат без слешей в начале и в конце.
     *
     * @return string
     */
    public function createPath() {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }
        $paths = array_map(function($element) {
            return trim($element, "/");
        }, $paths);
        $paths = array_filter($paths);
        return join('/', $paths);
    }

    /**
     * Объединить параметры в путь, который начинается со слеша.
     *
     * @param mixed ...$args
     * @return string
     */
    public function createSlashedPath(...$args)
    {
        return '/' . $this->createPath(...$args);
    }
}