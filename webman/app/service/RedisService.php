<?php

namespace app\service;

use support\Redis;
use app\common\Constants;

class RedisService
{
    public static function connection()
    {
        return Redis::connection();
    }

    public static function set($key, $value, $ttl = null)
    {
        $redis = self::connection();
        if ($ttl) {
            return $redis->setex($key, $ttl, is_string($value) ? $value : json_encode($value));
        }
        return $redis->set($key, is_string($value) ? $value : json_encode($value));
    }

    public static function get($key, $decode = false)
    {
        $redis = self::connection();
        $value = $redis->get($key);
        if ($value === null) return null;
        if ($decode) {
            return json_decode($value, true);
        }
        return $value;
    }

    public static function delete($key)
    {
        $redis = self::connection();
        return $redis->del($key);
    }

    public static function has($key)
    {
        $redis = self::connection();
        return $redis->exists($key);
    }

    public static function expire($key, $seconds)
    {
        $redis = self::connection();
        return $redis->expire($key, $seconds);
    }

    public static function keys($pattern)
    {
        $redis = self::connection();
        return $redis->keys($pattern);
    }

    public static function hSet($key, $field, $value)
    {
        $redis = self::connection();
        return $redis->hset($key, $field, is_string($value) ? $value : json_encode($value));
    }

    public static function hGet($key, $field)
    {
        $redis = self::connection();
        return $redis->hget($key, $field);
    }

    public static function hGetAll($key)
    {
        $redis = self::connection();
        return $redis->hgetall($key);
    }

    public static function incr($key)
    {
        $redis = self::connection();
        return $redis->incr($key);
    }

    public static function decr($key)
    {
        $redis = self::connection();
        return $redis->decr($key);
    }

    public static function getInfo()
    {
        $redis = self::connection();
        return $redis->info();
    }

    public static function dbSize()
    {
        $redis = self::connection();
        return $redis->dbsize();
    }

    public static function commandCount()
    {
        $redis = self::connection();
        $info = $redis->info('stats');
        return $info['total_commands_processed'] ?? 0;
    }

    public static function flushDb()
    {
        $redis = self::connection();
        return $redis->flushdb();
    }
}
