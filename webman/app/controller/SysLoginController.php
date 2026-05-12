<?php

namespace app\controller;

use support\Request;
use app\service\TokenService;
use app\service\CaptchaService;
use app\service\SysUserService;
use app\service\SysPermissionService;
use app\service\SysLogininforService;
use app\service\SysMenuService;
use app\service\PasswordService;
use app\service\IpService;
use app\service\UserAgentService;
use app\common\AjaxResult;
use app\common\Constants;
use app\common\LoginUser;
use app\common\Helpers;
use app\model\SysUser;

class SysLoginController
{
    public function login(Request $request)
    {
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        $code = $request->post('code', '');
        $uuid = $request->post('uuid', '');

        $captchaEnabled = \app\service\SysConfigService::selectCaptchaEnabled();
        if ($captchaEnabled) {
            if (empty($code) || empty($uuid)) {
                return AjaxResult::error('验证码不能为空');
            }
            if (!CaptchaService::validateCaptcha($code, $uuid)) {
                return AjaxResult::error('验证码错误或已过期');
            }
        }

        if (empty($username) || empty($password)) {
            return AjaxResult::error('用户名/密码不能为空');
        }

        $blackIPList = \app\service\SysConfigService::selectConfigByKey('sys.login.blackIPList');
        if (!empty($blackIPList)) {
            $ip = $request->getRealIp();
            $blackList = array_map('trim', explode(';', $blackIPList));
            foreach ($blackList as $pattern) {
                if (fnmatch($pattern, $ip)) {
                    return AjaxResult::error('很遗憾，访问已被禁止');
                }
            }
        }

        $userService = new SysUserService();
        $user = $userService->selectUserByUserName($username);

        if (!$user) {
            $this->recordLogininfor($username, false, '用户不存在');
            return AjaxResult::error('用户不存在');
        }

        if ($user->status === '1') {
            $this->recordLogininfor($username, false, '用户已被停用');
            return AjaxResult::error('用户已被停用，请联系管理员');
        }

        $pwdResult = PasswordService::validate($user, $password);
        if ($pwdResult !== true) {
            $this->recordLogininfor($username, false, $pwdResult);
            return AjaxResult::error($pwdResult);
        }

        $loginUser = new LoginUser();
        $loginUser->userId = $user->user_id;
        $loginUser->deptId = $user->dept_id;
        $loginUser->user = $user;

        $permissionService = new SysPermissionService();
        $loginUser->permissions = $permissionService->getMenuPermission($user);

        $tokenService = new TokenService();
        $token = $tokenService->createToken($loginUser);

        SysUser::where('user_id', $user->user_id)->update([
            'login_ip' => $request->getRealIp(),
            'login_date' => date('Y-m-d H:i:s'),
        ]);

        $this->recordLogininfor($username, true, '登录成功');

        return AjaxResult::success('操作成功', ['token' => $token]);
    }

    public function logout(Request $request)
    {
        $tokenService = new TokenService();
        $uuid = $tokenService->getUuidFromToken($request);
        if ($uuid) {
            $tokenService->removeToken($uuid);
        }
        return AjaxResult::success('退出成功');
    }

    public function getInfo(Request $request)
    {
        $loginUser = $request->loginUser;
        if (!$loginUser) {
            return AjaxResult::error('未登录', Constants::UNAUTHORIZED);
        }

        $userService = new SysUserService();
        $user = $userService->selectUserById($loginUser->userId);
        if (!$user) {
            return AjaxResult::error('用户不存在');
        }

        $permissionService = new SysPermissionService();
        $roles = $permissionService->getRolePermission($user);
        $permissions = $loginUser->permissions;

        $pwdChrtype = (int)\app\service\SysConfigService::selectConfigByKey('sys.account.chrtype');
        $isDefaultModifyPwd = (int)\app\service\SysConfigService::selectConfigByKey('sys.account.initPasswordModify');
        $passwordValidateDays = (int)\app\service\SysConfigService::selectConfigByKey('sys.account.passwordValidateDays');

        $isDefaultModifyPwdFlag = false;
        if ($isDefaultModifyPwd === 1) {
            $initPwd = \app\service\SysConfigService::selectConfigByKey('sys.user.initPassword');
            if (PasswordService::verify($initPwd ?: '123456', $user->password)) {
                $isDefaultModifyPwdFlag = true;
            }
        }

        $isPasswordExpired = false;
        if ($passwordValidateDays > 0 && $user->pwd_update_date) {
            $daysDiff = (time() - strtotime($user->pwd_update_date)) / 86400;
            if ($daysDiff > $passwordValidateDays) {
                $isPasswordExpired = true;
            }
        }

        $userData = $user->toArray();
        unset($userData['password']);
        $userData = Helpers::userToCamelCase($userData);

        return AjaxResult::success('', [
            'user' => $userData,
            'roles' => array_values($roles),
            'permissions' => array_values($permissions),
            'pwdChrtype' => $pwdChrtype,
            'isDefaultModifyPwd' => $isDefaultModifyPwdFlag,
            'isPasswordExpired' => $isPasswordExpired,
        ]);
    }

    public function getRouters(Request $request)
    {
        $loginUser = $request->loginUser;
        if (!$loginUser) {
            return AjaxResult::error('未登录', Constants::UNAUTHORIZED);
        }

        $menuService = new SysMenuService();
        $menus = $menuService->selectMenuTreeByUserId($loginUser->userId);
        $routers = $menuService->buildMenus($menus);

        return json(['code' => 200, 'msg' => '', 'data' => $routers]);
    }

    public function unlockscreen(Request $request)
    {
        $password = $request->post('password', '');
        $loginUser = $request->loginUser;
        if (!$loginUser) {
            return AjaxResult::error('未登录', Constants::UNAUTHORIZED);
        }

        $user = SysUser::find($loginUser->userId);
        if (!$user || !PasswordService::verify($password, $user->password)) {
            return AjaxResult::error('密码错误');
        }

        return AjaxResult::success();
    }

    private function recordLogininfor($username, $success, $msg)
    {
        try {
            $request = request();
            $ip = $request->getRealIp();
            $ua = $request->header('user-agent', '');

            $logininforService = new SysLogininforService();
            $logininforService->insertLogininfor([
                'user_name' => $username,
                'ipaddr' => $ip,
                'login_location' => IpService::getLocation($ip),
                'browser' => UserAgentService::getBrowser($ua),
                'os' => UserAgentService::getOS($ua),
                'status' => $success ? '0' : '1',
                'msg' => $msg,
                'login_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
        }
    }
}
