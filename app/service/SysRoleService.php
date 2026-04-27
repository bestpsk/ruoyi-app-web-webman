<?php

namespace app\service;

use app\model\SysRole;
use app\model\SysRoleMenu;
use app\model\SysRoleDept;
use app\model\SysUserRole;
use app\common\Constants;

class SysRoleService
{
    public function selectRoleList($params = [])
    {
        $query = SysRole::where('del_flag', '0');

        if (!empty($params['role_name'])) {
            $query->where('role_name', 'like', '%' . $params['role_name'] . '%');
        }
        if (!empty($params['role_key'])) {
            $query->where('role_key', 'like', '%' . $params['role_key'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }
        if (isset($params['begin_time']) && !empty($params['begin_time'])) {
            $query->where('create_time', '>=', $params['begin_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query->where('create_time', '<=', $params['end_time']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        $orderBy = $params['orderByColumn'] ?? 'role_sort';
        $isAsc = $params['isAsc'] ?? 'asc';
        $direction = strtolower($isAsc) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $direction);

        return $query->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectRoleById($roleId)
    {
        return SysRole::where('del_flag', '0')->find($roleId);
    }

    public function selectAllRoles()
    {
        return SysRole::where('del_flag', '0')->where('status', '0')->orderBy('role_sort')->get();
    }

    public function insertRole($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['del_flag'] = '0';
        $role = SysRole::create($data);

        if (!empty($data['menu_ids'])) {
            $this->insertRoleMenu($role->role_id, $data['menu_ids']);
        }

        return $role;
    }

    public function updateRole($data)
    {
        $roleId = $data['role_id'];
        $data['update_time'] = date('Y-m-d H:i:s');

        SysRoleMenu::where('role_id', $roleId)->delete();

        if (!empty($data['menu_ids'])) {
            $this->insertRoleMenu($roleId, $data['menu_ids']);
        }

        unset($data['menu_ids'], $data['dept_ids']);
        $result = SysRole::where('role_id', $roleId)->update($data);

        return $result;
    }

    public function deleteRoleByIds($roleIds)
    {
        if (in_array(1, $roleIds)) {
            return false;
        }
        foreach ($roleIds as $roleId) {
            SysRoleMenu::where('role_id', $roleId)->delete();
            SysRoleDept::where('role_id', $roleId)->delete();
            SysUserRole::where('role_id', $roleId)->delete();
        }
        return SysRole::whereIn('role_id', $roleIds)->update(['del_flag' => '2']);
    }

    public function authDataScope($roleId, $dataScope, $deptIds = [])
    {
        SysRole::where('role_id', $roleId)->update([
            'data_scope' => $dataScope,
            'update_time' => date('Y-m-d H:i:s'),
        ]);

        SysRoleDept::where('role_id', $roleId)->delete();

        if ($dataScope === '2' && !empty($deptIds)) {
            $this->insertRoleDept($roleId, $deptIds);
        }

        return true;
    }

    public function changeStatus($roleId, $status)
    {
        return SysRole::where('role_id', $roleId)->update([
            'status' => $status,
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function selectRolePermissionByUserId($userId)
    {
        $roles = SysRole::join('sys_user_role', 'sys_role.role_id', '=', 'sys_user_role.role_id')
            ->where('sys_user_role.user_id', $userId)
            ->where('sys_role.status', '0')
            ->where('sys_role.del_flag', '0')
            ->get();

        $permsSet = [];
        foreach ($roles as $role) {
            $roleKey = trim($role->role_key);
            if (strpos($roleKey, ',') !== false) {
                $permsSet = array_merge($permsSet, explode(',', $roleKey));
            } else {
                $permsSet[] = $roleKey;
            }
        }
        return array_unique($permsSet);
    }

    public function checkRoleNameUnique($roleName, $roleId = null)
    {
        $query = SysRole::where('role_name', $roleName)->where('del_flag', '0');
        if ($roleId) {
            $query->where('role_id', '!=', $roleId);
        }
        return $query->exists();
    }

    public function checkRoleKeyUnique($roleKey, $roleId = null)
    {
        $query = SysRole::where('role_key', $roleKey)->where('del_flag', '0');
        if ($roleId) {
            $query->where('role_id', '!=', $roleId);
        }
        return $query->exists();
    }

    public function allocatedUserList($roleId, $params = [])
    {
        $query = \app\model\SysUser::join('sys_user_role', 'sys_user.user_id', '=', 'sys_user_role.user_id')
            ->where('sys_user_role.role_id', $roleId)
            ->where('sys_user.del_flag', '0');

        if (!empty($params['user_name'])) {
            $query->where('sys_user.user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['phonenumber'])) {
            $query->where('sys_user.phonenumber', 'like', '%' . $params['phonenumber'] . '%');
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->select('sys_user.*')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function unallocatedUserList($roleId, $params = [])
    {
        $allocatedUserIds = SysUserRole::where('role_id', $roleId)->pluck('user_id')->toArray();

        $query = \app\model\SysUser::where('del_flag', '0')
            ->whereNotIn('user_id', $allocatedUserIds);

        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['phonenumber'])) {
            $query->where('phonenumber', 'like', '%' . $params['phonenumber'] . '%');
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function cancelAuthUser($userId, $roleId)
    {
        return SysUserRole::where('user_id', $userId)->where('role_id', $roleId)->delete();
    }

    public function cancelAuthUserAll($userIds, $roleId)
    {
        return SysUserRole::where('role_id', $roleId)->whereIn('user_id', $userIds)->delete();
    }

    public function selectAuthUserAll($userIds, $roleId)
    {
        $data = [];
        foreach ($userIds as $userId) {
            $exists = SysUserRole::where('user_id', $userId)->where('role_id', $roleId)->exists();
            if (!$exists) {
                $data[] = ['user_id' => $userId, 'role_id' => $roleId];
            }
        }
        if (!empty($data)) {
            SysUserRole::insert($data);
        }
        return true;
    }

    private function insertRoleMenu($roleId, $menuIds)
    {
        $data = [];
        foreach ($menuIds as $menuId) {
            $data[] = ['role_id' => $roleId, 'menu_id' => $menuId];
        }
        if (!empty($data)) {
            SysRoleMenu::insert($data);
        }
    }

    private function insertRoleDept($roleId, $deptIds)
    {
        $data = [];
        foreach ($deptIds as $deptId) {
            $data[] = ['role_id' => $roleId, 'dept_id' => $deptId];
        }
        if (!empty($data)) {
            SysRoleDept::insert($data);
        }
    }
}
