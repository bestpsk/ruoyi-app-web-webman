<?php

namespace app\controller\system;

use support\Request;
use app\service\SysUserService;
use app\service\PermissionService;
use app\common\AjaxResult;
use app\common\TableDataInfo;
use app\common\ExcelUtil;
use app\model\SysUser;

class SysUserController
{
    public function list(Request $request)
    {
        $params = convert_to_snake_case($request->all());
        $params['login_user'] = $request->loginUser;
        $service = new SysUserService();
        $result = $service->selectUserList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function export(Request $request)
    {
        $params = $request->all();
        $params['login_user'] = $request->loginUser;
        $params['pageSize'] = 10000;
        $service = new SysUserService();
        $result = $service->selectUserList($params);
        $list = $result->items();

        $excelUtil = new ExcelUtil(SysUser::class);
        return $excelUtil->exportExcel($list, '用户数据');
    }

    public function importData(Request $request)
    {
        $file = $request->file('file');
        $updateSupport = $request->post('updateSupport', 'false') === 'true' || $request->post('updateSupport', false) === true;

        if (!$file || !$file->isValid()) {
            return AjaxResult::error('上传文件无效');
        }

        $excelUtil = new ExcelUtil(SysUser::class);
        $userList = $excelUtil->importExcel($file);

        $operName = $request->loginUser->user->user_name ?? '';
        $service = new SysUserService();

        try {
            $message = $service->importUser($userList, $updateSupport, $operName);
            return AjaxResult::success($message);
        } catch (\Exception $e) {
            return AjaxResult::error($e->getMessage());
        }
    }

    public function importTemplate(Request $request)
    {
        $excelUtil = new ExcelUtil(SysUser::class);
        return $excelUtil->importTemplateExcel('用户数据');
    }

    public function getInfo(Request $request)
    {
        $userId = $request->input('user_id', 0);
        if (!$userId) {
            $parts = explode('/', $request->path());
            $userId = end($parts);
        }

        $roleService = new \app\service\SysRoleService();
        $roles = $roleService->selectAllRoles();
        $postService = new \app\service\SysPostService();
        $posts = $postService->selectPostAll();

        if (empty($userId) || $userId === 'system' || $userId === 'user' || !is_numeric($userId)) {
            return AjaxResult::success('', [
                'roles' => $roles,
                'posts' => $posts,
            ]);
        }

        $service = new SysUserService();
        $user = $service->selectUserById($userId);
        if (!$user) {
            return AjaxResult::error('用户不存在');
        }

        $userRoles = $user->roles->pluck('role_id')->toArray();
        $userPosts = $user->posts->pluck('post_id')->toArray();

        $userData = $user->toArray();
        unset($userData['password']);

        return AjaxResult::success('', [
            'data' => $userData,
            'roles' => $roles,
            'posts' => $posts,
            'roleIds' => $userRoles,
            'postIds' => $userPosts,
        ]);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysUserService();

        if ($service->checkUserNameUnique($data['user_name'] ?? '')) {
            return AjaxResult::error('新增用户\'' . ($data['user_name'] ?? '') . '\'失败，登录账号已存在');
        }
        if (!empty($data['phonenumber']) && $service->checkPhoneUnique($data['phonenumber'])) {
            return AjaxResult::error('新增用户\'' . ($data['user_name'] ?? '') . '\'失败，手机号码已存在');
        }
        if (!empty($data['email']) && $service->checkEmailUnique($data['email'])) {
            return AjaxResult::error('新增用户\'' . ($data['user_name'] ?? '') . '\'失败，邮箱已存在');
        }

        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $result = $service->insertUser($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysUserService();

        if ($service->checkUserNameUnique($data['user_name'] ?? '', $data['user_id'] ?? null)) {
            return AjaxResult::error('修改用户\'' . ($data['user_name'] ?? '') . '\'失败，登录账号已存在');
        }
        if (!empty($data['phonenumber']) && $service->checkPhoneUnique($data['phonenumber'], $data['user_id'] ?? null)) {
            return AjaxResult::error('修改用户\'' . ($data['user_name'] ?? '') . '\'失败，手机号码已存在');
        }
        if (!empty($data['email']) && $service->checkEmailUnique($data['email'], $data['user_id'] ?? null)) {
            return AjaxResult::error('修改用户\'' . ($data['user_name'] ?? '') . '\'失败，邮箱已存在');
        }

        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $result = $service->updateUser($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $userIds = explode(',', $request->input('userIds', ''));
        $userIds = array_map('intval', array_filter($userIds));
        if (in_array(1, $userIds)) {
            return AjaxResult::error('不允许删除超级管理员');
        }
        $service = new SysUserService();
        $result = $service->deleteUserByIds($userIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function resetPwd(Request $request)
    {
        $userId = $request->post('userId');
        $password = $request->post('password');
        if (empty($userId) || empty($password)) {
            return AjaxResult::error('参数错误');
        }
        $service = new SysUserService();
        $result = $service->resetPwd($userId, $password);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function changeStatus(Request $request)
    {
        $userId = $request->post('userId');
        $status = $request->post('status');
        $service = new SysUserService();
        $result = $service->changeStatus($userId, $status);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function profile(Request $request)
    {
        $loginUser = $request->loginUser;
        $service = new SysUserService();
        $user = $service->selectUserById($loginUser->userId);
        if (!$user) {
            return AjaxResult::error('用户不存在');
        }
        $userData = $user->toArray();
        unset($userData['password']);
        $roleGroup = $user->roles->pluck('role_name')->implode(',');
        $postGroup = $user->posts->pluck('post_name')->implode(',');
        return AjaxResult::success('', [
            'data' => $userData,
            'roleGroup' => $roleGroup,
            'postGroup' => $postGroup,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $loginUser = $request->loginUser;
        $data = convert_to_snake_case($request->post());
        $service = new SysUserService();

        if (!empty($data['phonenumber']) && $service->checkPhoneUnique($data['phonenumber'], $loginUser->userId)) {
            return AjaxResult::error('修改用户失败，手机号码已存在');
        }
        if (!empty($data['email']) && $service->checkEmailUnique($data['email'], $loginUser->userId)) {
            return AjaxResult::error('修改用户失败，邮箱已存在');
        }

        $result = $service->updateUserProfile($loginUser->userId, $data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function updatePwd(Request $request)
    {
        $loginUser = $request->loginUser;
        $oldPassword = $request->post('oldPassword', '');
        $newPassword = $request->post('newPassword', '');

        if (empty($oldPassword) || empty($newPassword)) {
            return AjaxResult::error('参数错误');
        }

        $user = \app\model\SysUser::find($loginUser->userId);
        if (!\app\service\PasswordService::verify($oldPassword, $user->password)) {
            return AjaxResult::error('修改密码失败，旧密码错误');
        }

        $service = new SysUserService();
        $result = $service->resetPwd($loginUser->userId, $newPassword);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function avatar(Request $request)
    {
        $loginUser = $request->loginUser;
        $file = $request->file('avatarfile');
        if (!$file || !$file->isValid()) {
            return AjaxResult::error('上传图片异常，请联系管理员');
        }

        $ext = $file->getUploadExtension() ?: 'png';
        $filename = md5(uniqid()) . '.' . $ext;
        $uploadDir = public_path() . '/profile/avatar/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $file->move($uploadDir . $filename);

        $avatarUrl = '/profile/avatar/' . $filename;
        \app\model\SysUser::where('user_id', $loginUser->userId)->update(['avatar' => $avatarUrl]);

        return AjaxResult::success('', ['imgUrl' => $avatarUrl]);
    }

    public function authRole(Request $request)
    {
        $parts = explode('/', $request->path());
        $userId = intval($parts[array_search('authRole', $parts) + 1] ?? 0);
        if (!$userId) {
            return AjaxResult::error('参数错误');
        }

        $userService = new SysUserService();
        $user = $userService->selectUserById($userId);
        if (!$user) {
            return AjaxResult::error('用户不存在');
        }

        $roleService = new \app\service\SysRoleService();
        $roles = $roleService->selectAllRoles();
        $userData = $user->toArray();
        unset($userData['password']);

        return AjaxResult::success('', [
            'user' => $userData,
            'roles' => $roles,
        ]);
    }

    public function insertAuthRole(Request $request)
    {
        $userId = $request->post('userId');
        $roleIds = $request->post('roleIds', []);
        if (is_string($roleIds)) {
            $roleIds = explode(',', $roleIds);
        }

        $userService = new SysUserService();
        $result = $userService->updateUser([
            'user_id' => $userId,
            'role_ids' => $roleIds,
        ]);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function deptTree(Request $request)
    {
        $deptService = new \app\service\SysDeptService();
        $depts = $deptService->selectDeptList([]);
        return AjaxResult::success($deptService->buildDeptTreeSelect($depts, 0));
    }
}
