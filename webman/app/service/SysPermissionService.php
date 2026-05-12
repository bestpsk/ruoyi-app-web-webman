<?php

namespace app\service;

use app\common\Constants;
use app\common\LoginUser;
use app\model\SysUser;
use app\model\SysRole;
use app\model\SysMenu;

class SysPermissionService
{
    public function getRolePermission(SysUser $user)
    {
        $roles = [];
        if ($user->isAdmin()) {
            $roles[] = Constants::SUPER_ADMIN;
        } else {
            $roleList = SysRole::whereHas('users', function ($q) use ($user) {
                $q->where('sys_user_role.user_id', $user->user_id);
            })->where('status', '0')->where('del_flag', '0')->get();

            foreach ($roleList as $role) {
                $roleKey = trim($role->role_key);
                if (strpos($roleKey, ',') !== false) {
                    $roles = array_merge($roles, explode(',', $roleKey));
                } else {
                    $roles[] = $roleKey;
                }
            }
        }
        return array_unique($roles);
    }

    public function getMenuPermission(SysUser $user)
    {
        $perms = [];
        if ($user->isAdmin()) {
            $perms[] = Constants::ALL_PERMISSION;
        } else {
            $menuPerms = SysMenu::join('sys_role_menu', 'sys_menu.menu_id', '=', 'sys_role_menu.menu_id')
                ->join('sys_user_role', 'sys_role_menu.role_id', '=', 'sys_user_role.role_id')
                ->join('sys_role', 'sys_user_role.role_id', '=', 'sys_role.role_id')
                ->where('sys_user_role.user_id', $user->user_id)
                ->where('sys_menu.status', '0')
                ->where('sys_role.status', '0')
                ->whereNotNull('sys_menu.perms')
                ->where('sys_menu.perms', '!=', '')
                ->pluck('sys_menu.perms')
                ->toArray();

            foreach ($menuPerms as $perm) {
                $perm = trim($perm);
                if (strpos($perm, ',') !== false) {
                    $perms = array_merge($perms, explode(',', $perm));
                } else {
                    $perms[] = $perm;
                }
            }
            $perms = array_unique($perms);
        }
        return array_values($perms);
    }

    public function getMenuPermsByRoleId($roleId)
    {
        $menuPerms = SysMenu::join('sys_role_menu', 'sys_menu.menu_id', '=', 'sys_role_menu.menu_id')
            ->where('sys_role_menu.role_id', $roleId)
            ->where('sys_menu.status', '0')
            ->whereNotNull('sys_menu.perms')
            ->where('sys_menu.perms', '!=', '')
            ->pluck('sys_menu.perms')
            ->toArray();

        $perms = [];
        foreach ($menuPerms as $perm) {
            $perm = trim($perm);
            if (strpos($perm, ',') !== false) {
                $perms = array_merge($perms, explode(',', $perm));
            } else {
                $perms[] = $perm;
            }
        }
        return array_unique($perms);
    }
}
