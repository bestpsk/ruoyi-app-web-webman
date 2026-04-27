<?php

namespace app\controller\system;

use support\Request;
use app\service\SysRoleService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysRoleController
{
    public function list(Request $request)
    {
        $service = new SysRoleService();
        $result = $service->selectRoleList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $roleId = intval(end($parts));
        $service = new SysRoleService();
        $role = $service->selectRoleById($roleId);
        if (!$role) {
            return AjaxResult::error('角色不存在');
        }
        return AjaxResult::success('', $role);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysRoleService();
        if ($service->checkRoleNameUnique($data['role_name'] ?? '')) {
            return AjaxResult::error('新增角色\'' . ($data['role_name'] ?? '') . '\'失败，角色名称已存在');
        }
        if ($service->checkRoleKeyUnique($data['role_key'] ?? '')) {
            return AjaxResult::error('新增角色\'' . ($data['role_name'] ?? '') . '\'失败，角色权限已存在');
        }
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $result = $service->insertRole($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysRoleService();
        if ($service->checkRoleNameUnique($data['role_name'] ?? '', $data['role_id'] ?? null)) {
            return AjaxResult::error('修改角色\'' . ($data['role_name'] ?? '') . '\'失败，角色名称已存在');
        }
        if ($service->checkRoleKeyUnique($data['role_key'] ?? '', $data['role_id'] ?? null)) {
            return AjaxResult::error('修改角色\'' . ($data['role_name'] ?? '') . '\'失败，角色权限已存在');
        }
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $result = $service->updateRole($data);

        $tokenService = new \app\service\TokenService();
        $tokenService->refreshPermissionByRoleId($data['role_id']);

        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $roleIds = explode(',', $request->input('roleIds', ''));
        $roleIds = array_map('intval', array_filter($roleIds));
        if (in_array(1, $roleIds)) {
            return AjaxResult::error('不允许删除超级管理员角色');
        }
        $service = new SysRoleService();
        $result = $service->deleteRoleByIds($roleIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function dataScope(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysRoleService();
        $result = $service->authDataScope(
            $data['role_id'] ?? 0,
            $data['data_scope'] ?? '1',
            $data['dept_ids'] ?? []
        );
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function changeStatus(Request $request)
    {
        $roleId = $request->post('roleId');
        $status = $request->post('status');
        $service = new SysRoleService();
        $result = $service->changeStatus($roleId, $status);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function optionselect(Request $request)
    {
        $service = new SysRoleService();
        return AjaxResult::success($service->selectAllRoles());
    }

    public function allocatedList(Request $request)
    {
        $parts = explode('/', $request->path());
        $roleId = 0;
        foreach ($parts as $i => $p) {
            if ($p === 'authUser' && isset($parts[$i - 1])) {
                $roleId = intval($parts[$i - 1]);
                break;
            }
        }
        $service = new SysRoleService();
        $result = $service->allocatedUserList($roleId, $request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function unallocatedList(Request $request)
    {
        $parts = explode('/', $request->path());
        $roleId = 0;
        foreach ($parts as $i => $p) {
            if ($p === 'authUser' && isset($parts[$i - 1])) {
                $roleId = intval($parts[$i - 1]);
                break;
            }
        }
        $service = new SysRoleService();
        $result = $service->unallocatedUserList($roleId, $request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function cancelAuthUser(Request $request)
    {
        $userId = $request->post('userId');
        $roleId = $request->post('roleId');
        $service = new SysRoleService();
        $result = $service->cancelAuthUser($userId, $roleId);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function cancelAuthUserAll(Request $request)
    {
        $roleId = $request->post('roleId');
        $userIds = $request->post('userIds', []);
        if (is_string($userIds)) {
            $userIds = explode(',', $userIds);
        }
        $service = new SysRoleService();
        $result = $service->cancelAuthUserAll($userIds, $roleId);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function selectAuthUserAll(Request $request)
    {
        $roleId = $request->post('roleId');
        $userIds = $request->post('userIds', []);
        if (is_string($userIds)) {
            $userIds = explode(',', $userIds);
        }
        $service = new SysRoleService();
        $result = $service->selectAuthUserAll($userIds, $roleId);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function deptTree(Request $request)
    {
        $parts = explode('/', $request->path());
        $roleId = intval(end($parts));
        $deptService = new \app\service\SysDeptService();
        $depts = $deptService->selectDeptList([]);
        $roleDeptService = new SysRoleService();
        $checkedKeys = \app\model\SysRoleDept::where('role_id', $roleId)->pluck('dept_id')->toArray();
        return AjaxResult::success('', [
            'depts' => $deptService->buildDeptTreeSelect($depts, 0),
            'checkedKeys' => $checkedKeys,
        ]);
    }
}
