<?php

namespace app\service;

use support\Redis;
use app\common\Constants;

class PasswordService
{
    public static function encrypt($password)
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function validate($loginUser, $password)
    {
        $redis = Redis::connection();
        $retryCountKey = Constants::PWD_ERR_CNT_KEY . $loginUser->user_name;
        $retryCount = (int)$redis->get($retryCountKey);

        if ($retryCount >= Constants::PWD_ERR_MAX_COUNT) {
            $ttl = $redis->ttl($retryCountKey);
            $minutes = max(1, ceil($ttl / 60));
            return "密码错误次数过多，账户锁定{$minutes}分钟";
        }

        if (!self::verify($password, $loginUser->password)) {
            $retryCount++;
            $redis->setex($retryCountKey, Constants::PWD_ERR_LOCK_TIME * 60, $retryCount);
            $remaining = Constants::PWD_ERR_MAX_COUNT - $retryCount;
            if ($remaining > 0) {
                return "密码错误，还剩{$remaining}次机会";
            }
            return "密码错误次数过多，账户锁定10分钟";
        }

        $redis->del($retryCountKey);
        return true;
    }

    public static function isDefaultPassword($password)
    {
        $initPassword = SysConfigService::selectConfigByKey('sys.user.initPassword');
        return $password === $initPassword;
    }
}
