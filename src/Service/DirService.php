<?php

namespace Pantheon\UserBundle\Service;

/**
 * Сервис, который работает с директориями.
 */
class DirService
{

    public function __construct()
    {
    }

    /**
     * Создать директорию.
     *
     * @param string $path
     * @return bool
     */
    public function createDir(string $path) : bool
    {
        $result = true;
        $old = umask(0);
        if (!$this->isDirExists($path)) {
            try {
                $result = mkdir($path, 0777, true);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage() . ' ' . $path);
            }
        }
        umask($old);
        return $result;
    }

    /**
     * Проверить, существует ли директория.
     *
     * @param string $path
     * @return bool
     */
    public function isDirExists(string $path) : bool
    {
        return (file_exists($path) and is_dir($path));
    }

    /**
     * Получить список файлов в директории.
     *
     * @param string $path
     * @return array
     */
    public function getFilesList(string $path) : array
    {
        return array_values(array_diff(scandir($path), array('..', '.')));
    }
}