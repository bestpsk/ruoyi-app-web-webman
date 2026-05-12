<?php

namespace app\controller\monitor;

use support\Request;
use support\Redis;
use app\service\RedisService;
use app\common\AjaxResult;

class CacheController
{
    public function getInfo(Request $request)
    {
        $redis = Redis::connection();
        $rawInfo = $redis->info();
        $info = $this->flattenInfo($rawInfo);
        $dbSize = $redis->dbsize();
        
        $commandStats = [];
        foreach ($rawInfo as $key => $value) {
            if (str_starts_with($key, 'cmdstat_')) {
                $cmd = str_replace('cmdstat_', '', $key);
                if (is_string($value)) {
                    $parts = explode(',', $value);
                    $calls = 0;
                    foreach ($parts as $part) {
                        if (str_starts_with(trim($part), 'calls=')) {
                            $calls = intval(str_replace('calls=', '', trim($part)));
                        }
                    }
                    $commandStats[] = ['name' => $cmd, 'value' => $calls];
                } elseif (is_array($value) && isset($value['calls'])) {
                    $commandStats[] = ['name' => $cmd, 'value' => intval($value['calls'])];
                }
            }
            
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (str_starts_with($subKey, 'cmdstat_')) {
                        $cmd = str_replace('cmdstat_', '', $subKey);
                        if (is_string($subValue)) {
                            $parts = explode(',', $subValue);
                            $calls = 0;
                            foreach ($parts as $part) {
                                if (str_starts_with(trim($part), 'calls=')) {
                                    $calls = intval(str_replace('calls=', '', trim($part)));
                                }
                            }
                            $commandStats[] = ['name' => $cmd, 'value' => $calls];
                        } elseif (is_array($subValue) && isset($subValue['calls'])) {
                            $commandStats[] = ['name' => $cmd, 'value' => intval($subValue['calls'])];
                        }
                    }
                }
            }
        }
        
        return AjaxResult::success('', [
            'data' => [
                'info' => $info,
                'dbSize' => $dbSize,
                'commandStats' => $commandStats,
            ]
        ]);
    }

    private function flattenInfo(array $nestedInfo): array
    {
        $flatInfo = [];
        
        foreach ($nestedInfo as $section => $data) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $snakeKey = $this->camelToSnake($key);
                    $flatInfo[$snakeKey] = $value;
                }
            } else {
                $snakeKey = $this->camelToSnake($section);
                $flatInfo[$snakeKey] = $data;
            }
        }
        
        return $flatInfo;
    }

    private function camelToSnake(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }

    public function getNames(Request $request)
    {
        $redis = Redis::connection();
        $keys = $redis->keys('*');
        $names = [];
        foreach ($keys as $key) {
            $parts = explode(':', $key);
            if (count($parts) > 1) {
                $name = $parts[0] . ':' . $parts[1];
                $exists = false;
                foreach ($names as $item) {
                    if ($item['cacheName'] === $name) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $names[] = ['cacheName' => $name, 'remark' => ''];
                }
            }
        }
        return AjaxResult::success('', [
            'data' => $names
        ]);
    }

    public function getKeys(Request $request)
    {
        $parts = explode('/', $request->path());
        $cacheName = end($parts);
        $redis = Redis::connection();
        $keys = $redis->keys($cacheName . '*');
        return AjaxResult::success('', [
            'data' => $keys
        ]);
    }

    public function getValue(Request $request)
    {
        $pathParts = explode('/', $request->path());
        $cacheName = $pathParts[count($pathParts) - 2] ?? '';
        $cacheKey = end($pathParts);
        $redis = Redis::connection();
        $fullKey = $cacheName . ':' . $cacheKey;
        $value = $redis->get($fullKey);
        if ($value === null) {
            $value = $redis->get($cacheKey);
        }
        return AjaxResult::success('', [
            'data' => [
                'cacheName' => $cacheName,
                'cacheKey' => $cacheKey,
                'cacheValue' => $value,
            ]
        ]);
    }

    public function clearCacheName(Request $request)
    {
        $parts = explode('/', $request->path());
        $cacheName = end($parts);
        $redis = Redis::connection();
        $keys = $redis->keys($cacheName . '*');
        foreach ($keys as $key) {
            $redis->del($key);
        }
        return AjaxResult::success();
    }

    public function clearCacheKey(Request $request)
    {
        $parts = explode('/', $request->path());
        $cacheKey = end($parts);
        $redis = Redis::connection();
        $redis->del($cacheKey);
        return AjaxResult::success();
    }

    public function clearCacheAll(Request $request)
    {
        $redis = Redis::connection();
        $redis->flushdb();
        return AjaxResult::success();
    }
}
