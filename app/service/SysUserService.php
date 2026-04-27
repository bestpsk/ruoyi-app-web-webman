<?php

namespace app\service;

use app\model\SysUser;
use app\model\SysUserRole;
use app\model\SysUserPost;
use app\common\LoginUser;
use app\common\Constants;

class SysUserService
{
    public function selectUserList($params = [])
    {
        $query = SysUser::with(['dept', 'roles']);

        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['phonenumber'])) {
            $query->where('phonenumber', 'like', '%' . $params['phonenumber'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }
        if (!empty($params['dept_id'])) {
            $deptIds = \app\model\SysDept::where('dept_id', $params['dept_id'])
                ->orWhereRaw("FIND_IN_SET(?, ancestors)", [$params['dept_id']])
                ->pluck('dept_id')
                ->toArray();
            $query->whereIn('dept_id', $deptIds);
        }
        if (isset($params['begin_time']) && !empty($params['begin_time'])) {
            $query->where('create_time', '>=', $params['begin_time']);
        }
        if (isset($params['end_time']) && !empty($params['end_time'])) {
            $query->where('create_time', '<=', $params['end_time']);
        }

        $query->where('del_flag', '0');

        if (!empty($params['data_scope']) && !empty($params['login_user'])) {
            $query = DataScopeService::applyDataScope($query, $params['login_user'], 'sys_dept', 'sys_user');
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        $orderBy = $params['orderByColumn'] ?? '';
        $isAsc = $params['isAsc'] ?? 'asc';

        if ($orderBy) {
            $direction = strtolower($isAsc) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($orderBy, $direction);
        } else {
            $query->orderBy('user_id', 'asc');
        }

        return $query->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectUserById($userId)
    {
        return SysUser::with(['dept', 'roles', 'posts'])->where('del_flag', '0')->find($userId);
    }

    public function selectUserByUserName($userName)
    {
        return SysUser::with(['dept', 'roles'])->where('user_name', $userName)->where('del_flag', '0')->first();
    }

    public function insertUser($data)
    {
        if (!empty($data['password'])) {
            $data['password'] = PasswordService::encrypt($data['password']);
        } else {
            $initPwd = SysConfigService::selectConfigByKey('sys.user.initPassword');
            $data['password'] = PasswordService::encrypt($initPwd ?: '123456');
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['del_flag'] = '0';

        $user = SysUser::create($data);

        if (!empty($data['role_ids'])) {
            $this->insertUserRole($user->user_id, $data['role_ids']);
        }
        if (!empty($data['post_ids'])) {
            $this->insertUserPost($user->user_id, $data['post_ids']);
        }

        return $user;
    }

    public function updateUser($data)
    {
        $userId = $data['user_id'];
        $data['update_time'] = date('Y-m-d H:i:s');

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = PasswordService::encrypt($data['password']);
        } else {
            unset($data['password']);
        }

        SysUserRole::where('user_id', $userId)->delete();
        SysUserPost::where('user_id', $userId)->delete();

        if (!empty($data['role_ids'])) {
            $this->insertUserRole($userId, $data['role_ids']);
        }
        if (!empty($data['post_ids'])) {
            $this->insertUserPost($userId, $data['post_ids']);
        }

        unset($data['role_ids'], $data['post_ids']);
        return SysUser::where('user_id', $userId)->update($data);
    }

    public function deleteUserByIds($userIds)
    {
        if (in_array(1, $userIds)) {
            return false;
        }
        SysUserRole::whereIn('user_id', $userIds)->delete();
        SysUserPost::whereIn('user_id', $userIds)->delete();
        return SysUser::whereIn('user_id', $userIds)->update(['del_flag' => '2']);
    }

    public function resetPwd($userId, $password)
    {
        return SysUser::where('user_id', $userId)->update([
            'password' => PasswordService::encrypt($password),
            'pwd_update_date' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function changeStatus($userId, $status)
    {
        return SysUser::where('user_id', $userId)->update([
            'status' => $status,
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function checkUserNameUnique($userName, $userId = null)
    {
        $query = SysUser::where('user_name', $userName)->where('del_flag', '0');
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        return $query->exists();
    }

    public function checkPhoneUnique($phone, $userId = null)
    {
        if (empty($phone)) return false;
        $query = SysUser::where('phonenumber', $phone)->where('del_flag', '0');
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        return $query->exists();
    }

    public function checkEmailUnique($email, $userId = null)
    {
        if (empty($email)) return false;
        $query = SysUser::where('email', $email)->where('del_flag', '0');
        if ($userId) {
            $query->where('user_id', '!=', $userId);
        }
        return $query->exists();
    }

    public function updateUserProfile($userId, $data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        unset($data['user_id'], $data['password'], $data['user_name']);
        return SysUser::where('user_id', $userId)->update($data);
    }

    private function insertUserRole($userId, $roleIds)
    {
        $data = [];
        foreach ($roleIds as $roleId) {
            $data[] = ['user_id' => $userId, 'role_id' => $roleId];
        }
        if (!empty($data)) {
            SysUserRole::insert($data);
        }
    }

    private function insertUserPost($userId, $postIds)
    {
        $data = [];
        foreach ($postIds as $postId) {
            $data[] = ['user_id' => $userId, 'post_id' => $postId];
        }
        if (!empty($data)) {
            SysUserPost::insert($data);
        }
    }

    public function importUser($userList, $isUpdateSupport, $operName)
    {
        if (empty($userList)) {
            throw new \Exception('导入用户数据不能为空！');
        }

        $successNum = 0;
        $failureNum = 0;
        $successMsg = '';
        $failureMsg = '';

        foreach ($userList as $user) {
            try {
                $userName = $user['user_name'] ?? '';
                if (empty($userName)) {
                    $failureNum++;
                    $failureMsg .= "<br/>{$failureNum}、账号不能为空";
                    continue;
                }

                $existingUser = $this->selectUserByUserName($userName);

                if (!$existingUser) {
                    $initPwd = SysConfigService::selectConfigByKey('sys.user.initPassword');
                    $user['password'] = PasswordService::encrypt($initPwd ?: '123456');
                    $user['create_by'] = $operName;
                    $user['nick_name'] = $user['nick_name'] ?? $userName;
                    $user['status'] = $user['status'] ?? '0';
                    $user['del_flag'] = '0';
                    $this->insertUser($user);
                    $successNum++;
                    $successMsg .= "<br/>{$successNum}、账号 {$userName} 导入成功";
                } else if ($isUpdateSupport) {
                    if ($existingUser->user_id == 1) {
                        $failureNum++;
                        $failureMsg .= "<br/>{$failureNum}、账号 {$userName} 是超级管理员，不允许更新";
                        continue;
                    }
                    $user['user_id'] = $existingUser->user_id;
                    $user['update_by'] = $operName;
                    $this->updateUser($user);
                    $successNum++;
                    $successMsg .= "<br/>{$successNum}、账号 {$userName} 更新成功";
                } else {
                    $failureNum++;
                    $failureMsg .= "<br/>{$failureNum}、账号 {$userName} 已存在";
                }
            } catch (\Exception $e) {
                $failureNum++;
                $failureMsg .= "<br/>{$failureNum}、账号 " . ($user['user_name'] ?? '未知') . " 导入失败：" . $e->getMessage();
            }
        }

        if ($failureNum > 0) {
            throw new \Exception("很抱歉，导入失败！共 {$failureNum} 条数据格式不正确，错误如下：{$failureMsg}");
        }

        return "恭喜您，数据已全部导入成功！共 {$successNum} 条，数据如下：{$successMsg}";
    }
}
