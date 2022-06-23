<?php

namespace Pantheon\UserBundle\Service;

/**
 * Класс, который работает с файлами на диске.
 */
class FileService
{
    public function __construct(
    )
    {
    }

    public function toBase64(string $path) : string
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}