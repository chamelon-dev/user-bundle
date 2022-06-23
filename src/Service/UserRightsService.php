<?php

namespace Pantheon\UserBundle\Service;

use Pantheon\UserBundle\Entity\Permission;
use Pantheon\UserBundle\Entity\Role;
use Pantheon\UserBundle\Entity\User;

/**
 * Класс, который возвращает роли и пермишны пользователя.
 * TODO: класс довольно общий, возможно, надо разделить получение ролей и пермишнов.
 * TODO: можно сделать кэш для уменьшения числа запросов к БД
 */
class UserRightsService
{
    public function __construct()
    {
    }

    /**
     * Получить роли пользователя (массив с объектами).
     *
     * @param User $user
     * @return Role[]
     */
    public function getRoles(User $user) : array
    {
        // TODO: не давать ли всем ROLE_USER
        // TODO: пожалуй, пока не будем
        return $user->getRole()->getValues();
    }

    /**
     * Получить роли пользователя (массив с текстовыми названиями).
     *
     * @param User $user
     * @return array
     */
    public function getRolesValues(User $user) : array
    {
        $result = [];
        foreach ($this->getRoles($user) as $role) {
            $name = $role->getName();
            $result[$name] = $role->getName();
        }
        return $result;
    }

    /**
     * Получить пермишны пользователя (массив с объектами).
     *
     * @param User $user
     * @return Permission[]
     */
    public function getPermissions(User $user) : array
    {
        $result = [];
        $roles = $this->getRoles($user);
        foreach ($roles as $role) {
            $rolePermissions = $role->getPermissions()->getValues();
            foreach ($rolePermissions as $permission) {
                $name = $permission->getName();
                $result[$name] = $permission;
            }
        }
        return $result;
    }

    /**
     * Получить пермишны пользователя (массив с текстовыми названиями).
     *
     * @param User $user
     * @return array
     */
    public function getPermissionsValues(User $user) : array
    {
        $result = [];
        $permissions = $this->getPermissions($user);
        foreach ($permissions as $permission) {
            $permissionName = $permission->getName();
            $result[$permissionName] = $permissionName;
        }
        return $result;
    }
}