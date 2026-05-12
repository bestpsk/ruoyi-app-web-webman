<?php

namespace app\service;

use app\common\Constants;
use app\common\LoginUser;

class PermissionService
{
    public static function hasPermi(LoginUser $loginUser, $permission)
    {
        if ($loginUser->isAdmin()) {
            return true;
        }
        $permissions = $loginUser->permissions ?? [];
        if (in_array(Constants::ALL_PERMISSION, $permissions)) {
            return true;
        }
        return in_array($permission, $permissions);
    }

    public static function lacksPermi(LoginUser $loginUser, $permission)
    {
        return !self::hasPermi($loginUser, $permission);
    }

    public static function hasAnyPermi(LoginUser $loginUser, $permissions)
    {
        if ($loginUser->isAdmin()) {
            return true;
        }
        $userPerms = $loginUser->permissions ?? [];
        if (in_array(Constants::ALL_PERMISSION, $userPerms)) {
            return true;
        }
        $permArr = is_array($permissions) ? $permissions : explode(',', $permissions);
        foreach ($permArr as $perm) {
            if (in_array(trim($perm), $userPerms)) {
                return true;
            }
        }
        return false;
    }

    public static function hasRole(LoginUser $loginUser, $role)
    {
        if ($loginUser->isAdmin()) {
            return true;
        }
        $userRoles = self::getUserRoles($loginUser);
        return in_array($role, $userRoles);
    }

    public static function lacksRole(LoginUser $loginUser, $role)
    {
        return !self::hasRole($loginUser, $role);
    }

    public static function hasAnyRoles(LoginUser $loginUser, $roles)
    {
        if ($loginUser->isAdmin()) {
            return true;
        }
        $userRoles = self::getUserRoles($loginUser);
        $roleArr = is_array($roles) ? $roles : explode(',', $roles);
        foreach ($roleArr as $r) {
            if (in_array(trim($r), $userRoles)) {
                return true;
            }
        }
        return false;
    }

    private static function getUserRoles(LoginUser $loginUser)
    {
        $roles = [];
        if ($loginUser->user && $loginUser->user->roles) {
            foreach ($loginUser->user->roles as $role) {
                $roleKey = trim($role['role_key'] ?? '');
                if (strpos($roleKey, ',') !== false) {
                    $roles = array_merge($roles, explode(',', $roleKey));
                } else {
                    $roles[] = $roleKey;
                }
            }
        }
        return array_unique($roles);
    }
}
