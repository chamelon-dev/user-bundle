<?php

namespace Pantheon\UserBundle\Service;

use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;

/**
 * Класс, возвращающий json-результат в едином виде.
 */
class ResultJsonService
{
    public function error($message = null) : array
    {
        $result = [
            'result' => 'error',
            'message' => $message ?? 'Error',
        ];
        return $result;
    }

    public function ok() : array
    {
        return [
            'result' => 'ok',
        ];
    }

}