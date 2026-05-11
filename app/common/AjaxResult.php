<?php

namespace app\common;

class AjaxResult
{
    public static function success($msg = '操作成功', $data = null)
    {
        if (is_array($msg) || is_object($msg)) {
            $data = $msg;
            $msg = '操作成功';
        }

        $result = ['code' => 200, 'msg' => $msg];
        if ($data !== null) {
            if (is_array($data)) {
                $data = self::convertToCamelCase($data);
                if (self::isAssociative($data)) {
                    $result = array_merge($result, $data);
                } else {
                    $result['data'] = $data;
                }
            } elseif (is_object($data)) {
                $result['data'] = self::convertToCamelCase($data->toArray());
            } else {
                $result['data'] = $data;
            }
        }
        return json($result);
    }

    public static function error($msg = '操作失败', $code = 500)
    {
        return json(['code' => $code, 'msg' => $msg]);
    }

    public static function warn($msg = '')
    {
        return json(['code' => 601, 'msg' => $msg]);
    }

    public static function result($code, $msg, $data = null)
    {
        $result = ['code' => $code, 'msg' => $msg];
        if ($data !== null) {
            if (is_array($data)) {
                $data = self::convertToCamelCase($data);
                if (self::isAssociative($data)) {
                    $result = array_merge($result, $data);
                } else {
                    $result['data'] = $data;
                }
            } elseif (is_object($data)) {
                $result['data'] = self::convertToCamelCase($data->toArray());
            } else {
                $result['data'] = $data;
            }
        }
        return json($result);
    }

    public static function toAjax($rows)
    {
        return $rows > 0 ? self::success() : self::error();
    }

    private static function isAssociative(array $arr)
    {
        if (empty($arr)) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private static function convertToCamelCase($data)
    {
        if ($data === null) {
            return null;
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(function ($item) {
                return self::convertToCamelCase($item);
            })->toArray();
        }

        if (is_object($data)) {
            $data = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $newKey = is_string($key) ? self::toCamelCase($key) : $key;
            if (is_array($value) || is_object($value)) {
                $result[$newKey] = self::convertToCamelCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private static function toCamelCase($key)
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }
}
