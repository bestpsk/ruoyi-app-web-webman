<?php

namespace app\service;

use support\Redis;
use app\common\Constants;
use app\common\LoginUser;

class SysUserOnlineService
{
    public function selectOnlineList($params = [])
    {
        $redis = Redis::connection();
        $keys = $redis->keys(Constants::LOGIN_TOKEN_KEY . '*');

        $onlineList = [];
        foreach ($keys as $key) {
            $data = $redis->get($key);
            if (!$data) continue;
            $loginUserArr = json_decode($data, true);
            $loginUser = LoginUser::fromArray($loginUserArr);

            if (!empty($params['ipaddr']) && strpos($loginUser->ipaddr, $params['ipaddr']) === false) {
                continue;
            }
            if (!empty($params['user_name']) && (!$loginUser->user || strpos($loginUser->user->user_name, $params['user_name']) === false)) {
                continue;
            }

            $dept = $loginUser->user->dept ?? null;
            $deptName = '';
            if ($dept) {
                $deptName = is_array($dept) ? ($dept['dept_name'] ?? '') : $dept->dept_name;
            }

            $onlineList[] = [
                'tokenId' => $loginUser->token,
                'userName' => $loginUser->user ? $loginUser->user->user_name : '',
                'deptName' => $deptName,
                'ipaddr' => $loginUser->ipaddr,
                'loginLocation' => $loginUser->loginLocation,
                'browser' => $loginUser->browser,
                'os' => $loginUser->os,
                'loginTime' => $loginUser->loginTime,
            ];
        }

        return $onlineList;
    }

    public function forceLogout($tokenId)
    {
        $redis = Redis::connection();
        $key = Constants::LOGIN_TOKEN_KEY . $tokenId;
        return $redis->del($key) > 0;
    }
}
