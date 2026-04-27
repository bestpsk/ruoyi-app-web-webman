<?php

namespace app\service;

use app\model\SysConfig;
use support\Redis;
use app\common\Constants;

class SysConfigService
{
    public static function selectConfigList($params = [])
    {
        $query = SysConfig::query();

        if (!empty($params['config_name'])) {
            $query->where('config_name', 'like', '%' . $params['config_name'] . '%');
        }
        if (!empty($params['config_key'])) {
            $query->where('config_key', 'like', '%' . $params['config_key'] . '%');
        }
        if (!empty($params['config_type'])) {
            $query->where('config_type', $params['config_type']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('config_id', 'asc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public static function selectConfigById($configId)
    {
        return SysConfig::find($configId);
    }

    public static function selectConfigByKey($configKey)
    {
        $redis = Redis::connection();
        $cacheKey = Constants::SYS_CONFIG_KEY . $configKey;
        $cached = $redis->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $config = SysConfig::where('config_key', $configKey)->first();
        if ($config) {
            $redis->set($cacheKey, $config->config_value);
            return $config->config_value;
        }
        return '';
    }

    public static function insertConfig($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $result = SysConfig::create($data);
        if (!empty($data['config_key'])) {
            $redis = Redis::connection();
            $redis->set(Constants::SYS_CONFIG_KEY . $data['config_key'], $data['config_value']);
        }
        return $result;
    }

    public static function updateConfig($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $result = SysConfig::where('config_id', $data['config_id'])->update($data);
        if (!empty($data['config_key'])) {
            $redis = Redis::connection();
            $redis->set(Constants::SYS_CONFIG_KEY . $data['config_key'], $data['config_value'] ?? '');
        }
        return $result;
    }

    public static function deleteConfigByIds($configIds)
    {
        $configs = SysConfig::whereIn('config_id', $configIds)->get();
        $redis = Redis::connection();
        foreach ($configs as $config) {
            $redis->del(Constants::SYS_CONFIG_KEY . $config->config_key);
        }
        return SysConfig::whereIn('config_id', $configIds)->delete();
    }

    public static function resetConfigCache()
    {
        $redis = Redis::connection();
        $keys = $redis->keys(Constants::SYS_CONFIG_KEY . '*');
        foreach ($keys as $key) {
            $redis->del($key);
        }
        $configs = SysConfig::all();
        foreach ($configs as $config) {
            $redis->set(Constants::SYS_CONFIG_KEY . $config->config_key, $config->config_value);
        }
    }

    public static function selectCaptchaEnabled()
    {
        $value = self::selectConfigByKey('sys.account.captchaEnabled');
        return $value === 'true';
    }
}
